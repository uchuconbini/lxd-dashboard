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
        $instances_list[] = [
          'name'         => $inst['name'],
          'type'         => isset($inst['type']) ? $inst['type'] : 'container',
          'status'       => isset($inst['state']['status']) ? $inst['state']['status'] : 'Unknown',
          'cpuUsage'     => isset($inst['state']['cpu']['usage'])            ? (float)$inst['state']['cpu']['usage']            : 0,
          'memUsage'     => isset($inst['state']['memory']['usage'])         ? (float)$inst['state']['memory']['usage']         : 0,
          'memUsagePeak' => isset($inst['state']['memory']['usage_peak'])    ? (float)$inst['state']['memory']['usage_peak']    : 0,
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

  }

} else {
  echo '{"error": "not authenticated", "error_code": "401", "metadata": {"err": "not authenticated", "status_code": "401"}}';
}
?>
