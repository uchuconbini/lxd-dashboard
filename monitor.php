<!--
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
-->

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="icon" type="image/png" href="assets/images/logo-light.svg">
  <title>LXD Dashboard - Monitoring</title>
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <link href="vendor/fonts/nunito.css" rel="stylesheet">
  <link href="vendor/sb-admin-2/css/sb-admin-2.css" rel="stylesheet">
  <link href="assets/css/style.css?version=3.0" rel="stylesheet">
  <style>
    .chart-area { position: relative; height: 300px; width: 100%; }
    .chart-pie  { position: relative; max-width: 220px; width: 100%; }
    .progress   { height: 6px; }
    .stat-badge-live    { color: #1cc88a; font-weight: 600; }
    .stat-badge-fetch   { color: #f6c23e; font-weight: 600; }
    .stat-badge-error   { color: #e74a3b; font-weight: 600; }
  </style>
</head>

<body id="page-top">

  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-dark sidebar sidebar-dark accordion sidebar-divider-right" id="accordionSidebar">
      <div id="sidebarLinks"></div>
      <hr class="sidebar-divider d-none d-md-block">
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle" onclick="setSidebarToggleValue()"></button>
      </div>
    </ul>

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>
          <div class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
            <ul class="navbar-nav ml-auto">
              <li class="nav-item dropdown no-arrow" id="notificationArea" style="display:none;">
                <div class="nav-link dropdown-toggle">
                  <span id="notification" class="mr-2 d-none d-lg-inline text-danger">Notification</span>
                </div>
              </li>
            </ul>
          </div>
          <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown"><label class="h6 mt-4 mr-2 ml-4">Host: </label></li>
            <li class="nav-item dropdown">
              <div class="input-group mt-3">
                <select class="form-control" id="remoteListNav" style="width:150px;" onchange="location = this.value;"></select>
              </div>
            </li>
            <li class="nav-item dropdown"><label class="h6 mt-4 mr-2 ml-4">Project: </label></li>
            <li class="nav-item dropdown">
              <div class="input-group mt-3">
                <select class="form-control" id="projectListNav" style="width:150px;" onchange="location = this.value;"></select>
              </div>
            </li>
            <div class="topbar-divider d-none d-sm-block"></div>
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user-circle fa-1x fa-fw mr-2 text-gray-600"></i>
                <span id="username" class="mr-2 d-none d-lg-inline text-gray-600"></span>
              </a>
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="user-profile.php"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>Profile</a>
                <a class="dropdown-item" href="settings.php"><i class="fas fa-cog fa-sm fa-fw mr-2 text-gray-400"></i>Settings</a>
                <a class="dropdown-item" href="logs.php"><i class="fas fa-history fa-sm fa-fw mr-2 text-gray-400"></i>Logs</a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#aboutModal"><i class="fas fa-info-circle fa-sm fa-fw mr-2 text-gray-400"></i>About</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" onclick="logout()"><i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>Logout</a>
              </div>
            </li>
          </ul>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid">

          <header class="page-header page-header-dark bg-gradient-primary-to-secondary">
            <div class="container-xl px-4">
              <div class="page-header-content pt-4">
                <div class="row align-items-center justify-content-between mt-n5 ml-n5 mr-n5 bg-dark pb-6">
                  <div class="col-auto mt-4 ml-3">
                    <div class="page-header-subtitle"><span>Host</span></div>
                    <h2 class="page-header-title mt-2" id="remoteBreadCrumb">MONITORING</h2>
                    <div class="page-header-subtitle">Real-time CPU, memory and top instance usage</div>
                  </div>
                  <div class="col-12 col-xl-auto mt-4">
                    <div class="input-group input-group-joined border-0" style="width:8rem;">
                      <span class="input-group-text bg-transparent border-0">
                        <a class="mr-2 h5" href="#" role="button" onclick="resetAndPoll()" title="Refresh">
                          <i class="fa fa-sync fa-lg fa-fw"></i>
                        </a>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </header>

          <div class="row mt-n5 ml-2 mr-2">
            <div class="col-12 mt-n3">

              <!-- Stat Cards -->
              <div class="row">

                <!-- CPU Usage -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3 py-2 mb-4">
                  <div class="card shadow h-100">
                    <div class="card-body">
                      <div class="row no-gutters align-items-center">
                        <div class="col-7 mt-n4">
                          <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">CPU Usage</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-600">
                            <i class="fas fa-microchip fa-1x mr-2 text-primary"></i>
                            <span id="statCpuLabel">—</span>
                          </div>
                          <div class="text-xs text-gray-500 mt-1"><span id="statCpuCores">—</span> logical CPUs</div>
                        </div>
                        <div class="col-5">
                          <div class="progress-circle-xs mx-auto" id="cpuGauge" data-value="0">
                            <span class="progress-circle-left-xs"><span class="progress-circle-bar-xs border-primary"></span></span>
                            <span class="progress-circle-right-xs"><span class="progress-circle-bar-xs border-primary"></span></span>
                            <div class="progress-circle-value-xs w-100 h-100 rounded-circle d-flex align-items-center justify-content-center mt-1">
                              <div class="h6 font-weight-bold" id="cpuGaugeLabel">—</div><sup class="small">%</sup>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Memory Usage -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3 py-2 mb-4">
                  <div class="card shadow h-100">
                    <div class="card-body">
                      <div class="row no-gutters align-items-center">
                        <div class="col-7 mt-n4">
                          <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Host Memory</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-600">
                            <i class="fas fa-memory fa-1x mr-2 text-primary"></i>
                            <span>used</span>
                          </div>
                          <div class="text-xs text-gray-500 mt-1"><span id="statMemUsed">—</span> / <span id="statMemTotal">—</span></div>
                        </div>
                        <div class="col-5">
                          <div class="progress-circle-xs mx-auto" id="memGauge" data-value="0">
                            <span class="progress-circle-left-xs"><span class="progress-circle-bar-xs border-primary"></span></span>
                            <span class="progress-circle-right-xs"><span class="progress-circle-bar-xs border-primary"></span></span>
                            <div class="progress-circle-value-xs w-100 h-100 rounded-circle d-flex align-items-center justify-content-center mt-1">
                              <div class="h6 font-weight-bold" id="memGaugeLabel">—</div><sup class="small">%</sup>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Running Instances -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3 py-2 mb-4">
                  <div class="card shadow h-100">
                    <div class="card-body">
                      <div class="row no-gutters align-items-center">
                        <div class="col-7 mt-n4">
                          <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Running Instances</div>
                          <div class="h5 mb-0 font-weight-bold text-gray-600">
                            <i class="fas fa-cube fa-1x mr-2 text-primary"></i>
                            <span id="statRunning">0</span> / <span id="statTotal">0</span>
                          </div>
                          <div class="text-xs text-gray-500 mt-1">containers &amp; VMs</div>
                        </div>
                        <div class="col-5">
                          <i class="fas fa-server fa-3x text-gray-200"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Poll Status -->
                <div class="col-sm-12 col-md-6 col-lg-6 col-xl-3 py-2 mb-4">
                  <div class="card shadow h-100">
                    <div class="card-body">
                      <div class="row no-gutters align-items-center">
                        <div class="col-7 mt-n4">
                          <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Data Feed</div>
                          <div class="h5 mb-0 font-weight-bold">
                            <i class="fas fa-circle fa-1x mr-2 text-primary"></i>
                            <span id="statStatus" class="stat-badge-fetch">Initializing</span>
                          </div>
                          <div class="text-xs text-gray-500 mt-1">Next: <span id="statNextPoll">—</span></div>
                        </div>
                        <div class="col-5">
                          <i class="fas fa-clock fa-3x text-gray-200"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div><!-- /stat cards row -->

              <!-- Charts Row -->
              <div class="row">

                <!-- CPU History Line Chart -->
                <div class="col-xl-8 col-lg-7 mb-4">
                  <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                      <h6 class="m-0 font-weight-bold text-primary">CPU Usage History</h6>
                      <span class="text-xs text-gray-500">Instance aggregate &bull; 5 s interval &bull; last 30 samples</span>
                    </div>
                    <div class="card-body">
                      <div class="chart-area">
                        <canvas id="cpuHistoryChart"></canvas>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Memory Doughnut -->
                <div class="col-xl-4 col-lg-5 mb-4">
                  <div class="card shadow h-100">
                    <div class="card-header py-3">
                      <h6 class="m-0 font-weight-bold text-primary">Memory Breakdown</h6>
                    </div>
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                      <div class="chart-pie">
                        <canvas id="memDoughnutChart"></canvas>
                      </div>
                      <div class="mt-3 text-center small">
                        <span class="mr-3">
                          <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#4e73df;"></span>
                          &nbsp;Used: <strong id="memLabelUsed">—</strong>
                        </span>
                        <span>
                          <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#e0e0e0;"></span>
                          &nbsp;Free: <strong id="memLabelFree">—</strong>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

              </div><!-- /charts row -->

              <!-- Top Usage Table -->
              <div class="row">
                <div class="col-12 mb-4">
                  <div class="card shadow">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                      <h6 class="m-0 font-weight-bold text-primary">Top Instance Usage</h6>
                      <span class="text-xs text-gray-500">Click a column header to sort</span>
                    </div>
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                          <thead class="thead-light">
                            <tr>
                              <th class="sortable" data-col="name" style="cursor:pointer;white-space:nowrap;">
                                Name <i class="fas fa-sort fa-xs text-gray-400 ml-1"></i>
                              </th>
                              <th>Type</th>
                              <th>Status</th>
                              <th class="sortable" data-col="cpu" style="cursor:pointer;white-space:nowrap;">
                                CPU % <i class="fas fa-sort fa-xs text-gray-400 ml-1"></i>
                              </th>
                              <th class="sortable" data-col="mem" style="cursor:pointer;white-space:nowrap;">
                                Memory Used <i class="fas fa-sort fa-xs text-gray-400 ml-1"></i>
                              </th>
                              <th class="sortable" data-col="memPct" style="cursor:pointer;white-space:nowrap;">
                                Memory % <i class="fas fa-sort fa-xs text-gray-400 ml-1"></i>
                              </th>
                            </tr>
                          </thead>
                          <tbody id="topUsageTableBody">
                            <tr><td colspan="6" class="text-center text-muted py-4">Collecting first sample&hellip;</td></tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div><!-- /top usage row -->

            </div>
          </div>

        </div><!-- /.container-fluid -->
      </div><!-- /#content -->

      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright &copy; LXDWARE 2020 - Present</span>
          </div>
        </div>
      </footer>
    </div><!-- /#content-wrapper -->
  </div><!-- /#wrapper -->

  <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

  <!-- About Modal -->
  <div class="modal fade" id="aboutModal" tabindex="-1" role="dialog" aria-labelledby="aboutModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="aboutModalLabel">About</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body"><div class="row"><div class="col-12"><div id="about"></div></div></div></div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Dismiss</button>
        </div>
      </div>
    </div>
  </div>

  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="vendor/sb-admin-2/js/sb-admin-2.min.js"></script>
  <script src="vendor/chart.js/chart.umd.min.js"></script>

</body>

<script>
  const queryString  = window.location.search;
  const urlParams    = new URLSearchParams(queryString);
  const remoteId     = urlParams.get('remote');
  const projectName  = urlParams.get('project');

  var prevSample   = null;   // { instances, timestamp }
  var lastStats    = null;   // latest raw stats from backend
  var lastCpuMap   = {};     // { name -> cpuPct }
  var cpuHistory   = [];
  var cpuLabels    = [];
  var MAX_POINTS   = 30;
  var POLL_MS      = 5000;
  var pollTimer    = null;
  var sortCol      = 'cpu';
  var sortAsc      = false;

  var cpuChart = null;
  var memChart = null;

  // ── Helpers ──────────────────────────────────────────────────────────────

  function logout() {
    $.get("./backend/aaa/authentication.php?action=deauthenticateUser", function(data) {
      if (JSON.parse(data).status_code == 200) window.location.href = './index.php';
    });
  }

  function formatBytes(bytes) {
    if (!bytes || bytes <= 0) return '0 B';
    if (bytes < 1048576)    return (bytes / 1024).toFixed(1)       + ' KiB';
    if (bytes < 1073741824) return (bytes / 1048576).toFixed(1)    + ' MiB';
    return                         (bytes / 1073741824).toFixed(2)  + ' GiB';
  }

  function pctToDeg(pct) { return pct / 100 * 360; }

  function formatProgressGauge() {
    $(".progress-circle-xs").each(function() {
      var val   = parseFloat($(this).attr('data-value')) || 0;
      var left  = $(this).find('.progress-circle-left-xs  .progress-circle-bar-xs');
      var right = $(this).find('.progress-circle-right-xs .progress-circle-bar-xs');
      if (val <= 50) {
        right.css('transform', 'rotate(' + pctToDeg(val) + 'deg)');
        left.css('transform',  'rotate(0deg)');
      } else {
        right.css('transform', 'rotate(180deg)');
        left.css('transform',  'rotate(' + pctToDeg(val - 50) + 'deg)');
      }
    });
  }

  function setStatus(text, cls) {
    $('#statStatus').removeClass('stat-badge-live stat-badge-fetch stat-badge-error').addClass(cls).text(text);
  }

  // ── Chart initialisation ─────────────────────────────────────────────────

  function initCharts() {
    var cpuCtx = document.getElementById('cpuHistoryChart').getContext('2d');
    cpuChart = new Chart(cpuCtx, {
      type: 'line',
      data: {
        labels: [],
        datasets: [{
          label: 'CPU %',
          data: [],
          borderColor: '#4e73df',
          backgroundColor: 'rgba(78,115,223,0.08)',
          borderWidth: 2,
          pointRadius: 2,
          pointHoverRadius: 4,
          fill: true,
          tension: 0.35
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            min: 0, max: 100,
            ticks: { callback: function(v) { return v + '%'; }, stepSize: 20 },
            grid:  { color: 'rgba(0,0,0,0.05)' }
          },
          x: { grid: { color: 'rgba(0,0,0,0.05)' } }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: { label: function(ctx) { return ' ' + ctx.parsed.y.toFixed(2) + '%'; } }
          }
        },
        animation: { duration: 300 }
      }
    });

    var memCtx = document.getElementById('memDoughnutChart').getContext('2d');
    memChart = new Chart(memCtx, {
      type: 'doughnut',
      data: {
        labels: ['Used', 'Free'],
        datasets: [{
          data: [0, 100],
          backgroundColor: ['#4e73df', '#e0e0e0'],
          hoverBackgroundColor: ['#2e59d9', '#c8c8c8'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        cutout: '72%',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: { label: function(ctx) { return ' ' + ctx.label + ': ' + ctx.parsed.toFixed(1) + '%'; } }
          }
        },
        animation: { duration: 300 }
      }
    });
  }

  // ── Table rendering ───────────────────────────────────────────────────────

  function renderTable(instances, cpuMap, memTotal) {
    var rows = instances.map(function(inst) {
      var cpuPct = (cpuMap[inst.name] !== undefined) ? cpuMap[inst.name] : null;
      var memPct = (memTotal > 0 && inst.memUsage > 0) ? (inst.memUsage / memTotal * 100) : 0;
      return { name: inst.name, type: inst.type, status: inst.status,
               cpuPct: cpuPct, memUsage: inst.memUsage, memPct: memPct };
    });

    rows.sort(function(a, b) {
      if (sortCol === 'name') {
        return sortAsc ? a.name.localeCompare(b.name) : b.name.localeCompare(a.name);
      }
      var av, bv;
      if (sortCol === 'cpu')    { av = a.cpuPct  !== null ? a.cpuPct  : -1; bv = b.cpuPct  !== null ? b.cpuPct  : -1; }
      if (sortCol === 'mem')    { av = a.memUsage; bv = b.memUsage; }
      if (sortCol === 'memPct') { av = a.memPct;   bv = b.memPct;   }
      return sortAsc ? av - bv : bv - av;
    });

    var tbody = $('#topUsageTableBody');
    tbody.empty();

    if (rows.length === 0) {
      tbody.append('<tr><td colspan="6" class="text-center text-muted py-4">No instances found</td></tr>');
      return;
    }

    rows.forEach(function(r) {
      var badge;
      if      (r.status === 'Running') badge = '<span class="badge badge-success">Running</span>';
      else if (r.status === 'Stopped') badge = '<span class="badge badge-secondary">Stopped</span>';
      else                             badge = '<span class="badge badge-warning">' + r.status + '</span>';

      var cpuCell;
      if (r.cpuPct !== null) {
        var c = r.cpuPct.toFixed(2);
        var barCls = r.cpuPct > 80 ? 'bg-danger' : r.cpuPct > 50 ? 'bg-warning' : 'bg-primary';
        cpuCell = '<div class="d-flex align-items-center">' +
          '<span class="mr-2" style="min-width:52px;">' + c + '%</span>' +
          '<div class="progress flex-grow-1"><div class="progress-bar ' + barCls + '" style="width:' + Math.min(100, r.cpuPct).toFixed(1) + '%"></div></div>' +
          '</div>';
      } else {
        cpuCell = '<span class="text-muted">—</span>';
      }

      var memCell, memPctCell;
      if (r.memUsage > 0) {
        var barCls2 = r.memPct > 80 ? 'bg-danger' : r.memPct > 50 ? 'bg-warning' : 'bg-info';
        memCell    = formatBytes(r.memUsage);
        memPctCell = '<div class="d-flex align-items-center">' +
          '<span class="mr-2" style="min-width:52px;">' + r.memPct.toFixed(1) + '%</span>' +
          '<div class="progress flex-grow-1"><div class="progress-bar ' + barCls2 + '" style="width:' + Math.min(100, r.memPct).toFixed(1) + '%"></div></div>' +
          '</div>';
      } else {
        memCell    = '<span class="text-muted">—</span>';
        memPctCell = '<span class="text-muted">—</span>';
      }

      var typeIcon = (r.type === 'virtual-machine')
        ? '<i class="fas fa-desktop fa-sm mr-1 text-gray-400"></i>'
        : '<i class="fas fa-cube fa-sm mr-1 text-gray-400"></i>';

      tbody.append(
        '<tr>' +
        '<td>' + r.name + '</td>' +
        '<td>' + typeIcon + r.type + '</td>' +
        '<td>' + badge + '</td>' +
        '<td>' + cpuCell + '</td>' +
        '<td>' + memCell + '</td>' +
        '<td>' + memPctCell + '</td>' +
        '</tr>'
      );
    });
  }

  // ── Polling ───────────────────────────────────────────────────────────────

  function pollStats() {
    clearTimeout(pollTimer);
    setStatus('Fetching…', 'stat-badge-fetch');

    $.get(
      "./backend/lxd/monitor.php?remote=" + encodeURI(remoteId) +
      "&project=" + encodeURI(projectName) + "&action=displayStats",
      function(raw) {
        var stats;
        try { stats = JSON.parse(raw); } catch(e) { setStatus('Parse error', 'stat-badge-error'); scheduleNextPoll(); return; }

        var now       = new Date();
        var timeLabel = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
        var cpuMap    = {};
        var hostCpuPct = 0;

        if (prevSample !== null) {
          var dtNs   = (stats.timestamp - prevSample.timestamp) * 1e6;
          var nCpus  = Math.max(1, stats.cpuTotal);

          if (dtNs > 0) {
            var totalDelta = 0;
            stats.instances.forEach(function(inst) {
              var prev = prevSample.instances.find(function(p) { return p.name === inst.name; });
              if (prev && inst.status === 'Running') {
                var delta = inst.cpuUsage - prev.cpuUsage;
                if (delta >= 0) {
                  var pct = (delta / dtNs / nCpus) * 100;
                  cpuMap[inst.name] = parseFloat(Math.min(100, Math.max(0, pct)).toFixed(2));
                  totalDelta += delta;
                }
              }
            });

            hostCpuPct = parseFloat(
              Math.min(100, Math.max(0, (totalDelta / dtNs / nCpus) * 100)).toFixed(2)
            );

            // Append to history chart
            cpuHistory.push(hostCpuPct);
            cpuLabels.push(timeLabel);
            if (cpuHistory.length > MAX_POINTS) { cpuHistory.shift(); cpuLabels.shift(); }
            cpuChart.data.labels            = cpuLabels;
            cpuChart.data.datasets[0].data  = cpuHistory;
            cpuChart.update();
          }
        }

        // CPU gauge
        $('#cpuGaugeLabel').text(hostCpuPct);
        $('#statCpuLabel').text(hostCpuPct + '%');
        $('#statCpuCores').text(stats.cpuTotal);
        $('#cpuGauge').attr('data-value', hostCpuPct);

        // Memory
        var memPct = stats.memTotal > 0
          ? parseFloat((stats.memUsed / stats.memTotal * 100).toFixed(2))
          : 0;
        $('#memGaugeLabel').text(memPct);
        $('#memGauge').attr('data-value', memPct);
        $('#statMemUsed').text(formatBytes(stats.memUsed));
        $('#statMemTotal').text(formatBytes(stats.memTotal));
        $('#memLabelUsed').text(formatBytes(stats.memUsed) + ' (' + memPct + '%)');
        $('#memLabelFree').text(formatBytes(stats.memTotal - stats.memUsed));
        memChart.data.datasets[0].data = [memPct, 100 - memPct];
        memChart.update();

        // Running count
        var running = stats.instances.filter(function(i) { return i.status === 'Running'; }).length;
        $('#statRunning').text(running);
        $('#statTotal').text(stats.instances.length);

        // Persist for table re-sort
        lastStats  = stats;
        lastCpuMap = cpuMap;

        // Render table
        renderTable(stats.instances, cpuMap, stats.memTotal);

        // Update progress gauges
        formatProgressGauge();

        prevSample = { instances: stats.instances, timestamp: stats.timestamp };
        setStatus('Live', 'stat-badge-live');
        scheduleNextPoll();
      }
    ).fail(function() {
      setStatus('Error', 'stat-badge-error');
      scheduleNextPoll();
    });
  }

  function scheduleNextPoll() {
    var next = new Date(Date.now() + POLL_MS);
    $('#statNextPoll').text(pad(next.getHours()) + ':' + pad(next.getMinutes()) + ':' + pad(next.getSeconds()));
    pollTimer = setTimeout(pollStats, POLL_MS);
  }

  function pad(n) { return String(n).padStart(2, '0'); }

  function resetAndPoll() {
    clearTimeout(pollTimer);
    prevSample  = null;
    cpuHistory  = [];
    cpuLabels   = [];
    if (cpuChart) { cpuChart.data.labels = []; cpuChart.data.datasets[0].data = []; cpuChart.update(); }
    pollStats();
  }

  // ── Sidebar helpers ───────────────────────────────────────────────────────

  function setSidebarToggleValue() {
    var state = localStorage.getItem('sidebarState');
    localStorage.setItem('sidebarState', state === 'collapsed' ? 'expanded' : 'collapsed');
  }

  function applySidebarToggleValue() {
    if (localStorage.getItem('sidebarState') === 'collapsed') {
      $("body").toggleClass("sidebar-toggled");
      $(".sidebar").toggleClass("toggled");
      if ($(".sidebar").hasClass("toggled")) $(".sidebar .collapse").collapse("hide");
    }
  }

  // ── Sort on header click (re-render with cached data) ─────────────────────

  $(document).on('click', '.sortable', function() {
    var col = $(this).data('col');
    sortAsc = (sortCol === col) ? !sortAsc : false;
    sortCol = col;
    if (lastStats) renderTable(lastStats.instances, lastCpuMap, lastStats.memTotal);
  });

  // ── Bootstrap ────────────────────────────────────────────────────────────

  applySidebarToggleValue();

  $(document).ready(function() {

    $.get("./backend/aaa/authentication.php?action=validateAuthentication", function(data) {
      if (JSON.parse(data).status_code != 200) window.location.href = './index.php';
    });

    $("#sidebarLinks").load("./sidebar.php?version=3.0");
    $('#remoteBreadCrumb').load("./backend/lxd/remote-breadcrumb.php?remote=" + encodeURI(remoteId));
    $("#username").load("./backend/admin/settings.php?action=displayUsername");

    $.get("./backend/lxd/remotes-single.php?remote=" + encodeURI(remoteId) + "&action=validateRemoteConnection", function(data) {
      var d = JSON.parse(data);
      if (d.status_code == 200) {
        $("#remoteListNav").load("./backend/lxd/remotes.php?remote=" + encodeURI(remoteId) + "&project=" + encodeURI(projectName) + "&action=listRemotesForSelectOption");
        $("#projectListNav").load("./backend/lxd/projects.php?remote=" + encodeURI(remoteId) + "&project=" + encodeURI(projectName) + "&action=listProjectsForSelectOption");
        initCharts();
        pollStats();
      } else {
        alert("Unable to connect to remote host. HTTP status code: " + d.status_code);
      }
    });

  });
</script>

</html>
