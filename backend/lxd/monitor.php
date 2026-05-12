<?php
/*
LXDWARE LXD Dashboard - A web-based interface for managing LXD servers
Copyright (C) 2020-2021  LXDWARE.COM

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!isset($_SESSION)) {
  session_start();
}

if (isset($_SESSION['username'])) {

  $action  = isset($_GET['action'])  ? filter_var(urldecode($_GET['action']),  FILTER_SANITIZE_STRING) : "";
  $project = isset($_GET['project']) ? filter_var(urldecode($_GET['project']), FILTER_SANITIZE_STRING) : "";
  $remote  = isset($_GET['remote'])  ? filter_var(urldecode($_GET['remote']),  FILTER_SANITIZE_NUMBER_INT) : "";

  require_once('../config/curl.php');
  require_once('../config/db.php');

  $base_url = retrieveHostURL($remote);

  switch ($action) {

    case "displayStats":
      $arr = [];
      $instances_list = [];

      // Fetch all instances with full state (recursion=2)
      $url = $base_url . "/1.0/instances?recursion=2&project=" . $project;
      $raw = sendCurlRequest($action, "GET", $url);
      $data = json_decode($raw, true);
      $instances = isset($data['metadata']) ? $data['metadata'] : [];

      foreach ($instances as $inst) {
        // Aggregate network bytes across all non-loopback interfaces
        $net_rx = 0;
        $net_tx = 0;
        if (isset($inst['state']['network'])) {
          foreach ($inst['state']['network'] as $iface => $idata) {
            if ($iface === 'lo') continue;
            $net_rx += isset($idata['counters']['bytes_received']) ? (float)$idata['counters']['bytes_received'] : 0;
            $net_tx += isset($idata['counters']['bytes_sent'])     ? (float)$idata['counters']['bytes_sent']     : 0;
          }
        }

        // Aggregate disk usage across all devices
        $disk_usage = 0;
        if (isset($inst['state']['disk'])) {
          foreach ($inst['state']['disk'] as $ddata) {
            $disk_usage += isset($ddata['usage']) ? (float)$ddata['usage'] : 0;
          }
        }

        $instances_list[] = [
          'name'         => $inst['name'],
          'type'         => isset($inst['type']) ? $inst['type'] : 'container',
          'status'       => isset($inst['state']['status']) ? $inst['state']['status'] : 'Unknown',
          'cpuUsage'     => isset($inst['state']['cpu']['usage'])         ? (float)$inst['state']['cpu']['usage']         : 0,
          'memUsage'     => isset($inst['state']['memory']['usage'])      ? (float)$inst['state']['memory']['usage']      : 0,
          'memUsagePeak' => isset($inst['state']['memory']['usage_peak']) ? (float)$inst['state']['memory']['usage_peak'] : 0,
          'netRx'        => $net_rx,
          'netTx'        => $net_tx,
          'diskUsage'    => $disk_usage,
        ];
      }

      // Fetch host resources for CPU count and total/used memory
      $url = $base_url . "/1.0/resources";
      $raw = sendCurlRequest($action, "GET", $url);
      $res = json_decode($raw, true);
      $resources = isset($res['metadata']) ? $res['metadata'] : [];

      $arr['instances'] = $instances_list;
      $arr['cpuTotal']  = isset($resources['cpu']['total'])    ? (int)$resources['cpu']['total']    : 1;
      $arr['memTotal']  = isset($resources['memory']['total']) ? (float)$resources['memory']['total'] : 0;
      $arr['memUsed']   = isset($resources['memory']['used'])  ? (float)$resources['memory']['used']  : 0;
      $arr['timestamp'] = round(microtime(true) * 1000); // ms, used client-side for CPU% delta

      echo json_encode($arr);
      break;

    case "saveHistory":
      // POST: persist one computed snapshot row
      $cpu_pct    = isset($_POST['cpu_pct'])    ? (float)$_POST['cpu_pct']    : 0;
      $mem_used   = isset($_POST['mem_used'])   ? (float)$_POST['mem_used']   : 0;
      $mem_total  = isset($_POST['mem_total'])  ? (float)$_POST['mem_total']  : 0;
      $net_rx_bps = isset($_POST['net_rx_bps']) ? (float)$_POST['net_rx_bps'] : 0;
      $net_tx_bps = isset($_POST['net_tx_bps']) ? (float)$_POST['net_tx_bps'] : 0;

      $db = establishDatabaseConnection();

      if ($_SESSION['db_type'] == "SQLite") {
        $db->exec('CREATE TABLE IF NOT EXISTS lxd_resource_history (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          remote_id INTEGER,
          recorded_at INTEGER,
          cpu_pct REAL,
          mem_used REAL,
          mem_total REAL,
          net_rx_bps REAL,
          net_tx_bps REAL
        )');
      } else {
        $db->exec('CREATE TABLE IF NOT EXISTS lxd_resource_history (
          id INT PRIMARY KEY AUTO_INCREMENT,
          remote_id INT,
          recorded_at INT,
          cpu_pct FLOAT,
          mem_used DOUBLE,
          mem_total DOUBLE,
          net_rx_bps DOUBLE,
          net_tx_bps DOUBLE
        )');
      }

      $stmt = $db->prepare('INSERT INTO lxd_resource_history
        (remote_id, recorded_at, cpu_pct, mem_used, mem_total, net_rx_bps, net_tx_bps)
        VALUES (?, ?, ?, ?, ?, ?, ?)');
      $stmt->execute([(int)$remote, time(), $cpu_pct, $mem_used, $mem_total, $net_rx_bps, $net_tx_bps]);

      // Prune rows older than 7 days (~1-in-50 chance per insert to avoid overhead)
      if (rand(1, 50) === 1) {
        $cutoff = time() - 604800;
        $db->prepare('DELETE FROM lxd_resource_history WHERE remote_id = ? AND recorded_at < ?')
           ->execute([(int)$remote, $cutoff]);
      }

      $db = null;
      echo json_encode(['status' => 'ok']);
      break;

    case "loadHistory":
      $range = isset($_GET['range']) ? (int)$_GET['range'] : 3600;

      // Bucket size keeps result under ~300 points
      if      ($range <= 3600)   $bucket = 30;
      elseif  ($range <= 21600)  $bucket = 120;
      elseif  ($range <= 86400)  $bucket = 300;
      else                       $bucket = 1800;

      $since = time() - $range;

      $db = establishDatabaseConnection();

      // Create table if it doesn't exist yet (first time before any saves)
      if ($_SESSION['db_type'] == "SQLite") {
        $db->exec('CREATE TABLE IF NOT EXISTS lxd_resource_history (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          remote_id INTEGER,
          recorded_at INTEGER,
          cpu_pct REAL,
          mem_used REAL,
          mem_total REAL,
          net_rx_bps REAL,
          net_tx_bps REAL
        )');
      } else {
        $db->exec('CREATE TABLE IF NOT EXISTS lxd_resource_history (
          id INT PRIMARY KEY AUTO_INCREMENT,
          remote_id INT,
          recorded_at INT,
          cpu_pct FLOAT,
          mem_used DOUBLE,
          mem_total DOUBLE,
          net_rx_bps DOUBLE,
          net_tx_bps DOUBLE
        )');
      }

      $stmt = $db->prepare('
        SELECT
          (recorded_at / :bucket) * :bucket AS ts,
          ROUND(AVG(cpu_pct),    2) AS cpu_pct,
          AVG(mem_used)             AS mem_used,
          AVG(mem_total)            AS mem_total,
          ROUND(AVG(net_rx_bps), 0) AS net_rx_bps,
          ROUND(AVG(net_tx_bps), 0) AS net_tx_bps
        FROM lxd_resource_history
        WHERE remote_id = :remote AND recorded_at >= :since
        GROUP BY (recorded_at / :bucket)
        ORDER BY ts ASC
      ');
      $stmt->execute([':bucket' => $bucket, ':remote' => (int)$remote, ':since' => $since]);
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $db = null;
      echo json_encode(['data' => $rows, 'bucket' => $bucket, 'range' => $range]);
      break;

  }

} else {
  echo '{"error": "not authenticated", "error_code": "401", "metadata": {"err": "not authenticated", "status_code": "401"}}';
}
?>
