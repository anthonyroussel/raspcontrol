<?php

namespace lib;
use lib\Uptime;
use lib\Memory;
use lib\CPU;
use lib\Storage;
use lib\Network;
use lib\Rbpi;
use lib\Users;
use lib\Temp;


spl_autoload_extensions('.php');
spl_autoload_register();

session_start();

error_reporting(0);

$uptime = Uptime::uptime();
$ram = Memory::ram();
$swap = Memory::swap();
$cpu = CPU::cpu();
$cpu_heat = CPU::heat();
$hdd = Storage::hdd();
$net_connections = Network::connections();
$net_eth = Network::ethernet();
$users = Users::connected();
$temp = Temp::temp();

function icon_alert($alert) {
  echo '<i class="icon-';
  switch($alert) {
    case 'success':
      echo 'ok';
      break;
    case 'warning':
      echo 'warning-sign';
      break;
    default:
      echo 'exclamation-sign';
  }
  echo '"></i>';
}

function shell_to_html_table_result($shellExecOutput) {
	$shellExecOutput = preg_split('/[\r\n]+/', $shellExecOutput);

	// remove double (or more) spaces for all items
	foreach ($shellExecOutput as &$item) {
		$item = preg_replace('/[[:blank:]]+/', ' ', $item);
		$item = trim($item);
	}

	// remove empty lines
	$shellExecOutput = array_filter($shellExecOutput);

	// the first line contains titles
	$columnCount = preg_match_all('/\s+/', $shellExecOutput[0]);
	$shellExecOutput[0] = '<tr><th>' . preg_replace('/\s+/', '</th><th>', $shellExecOutput[0], $columnCount) . '</th></tr>';
	$tableHead = $shellExecOutput[0];
	unset($shellExecOutput[0]);

	// others lines contains table lines
	foreach ($shellExecOutput as &$item) {
		$item = '<tr><td>' . preg_replace('/\s+/', '</td><td>', $item, $columnCount) . '</td></tr>';
	}

	// return the build table
	return '<table class=\'table table-striped\'>'
				. '<thead>' . $tableHead . '</thead>'
				. '<tbody>' . implode($shellExecOutput) . '</tbody>'
			. '</table>';
}


?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <title>Raspcontrol</title>
    <meta name="author" content="Nicolas Devenet" />
    <meta name="robots" content="noindex, nofollow, noarchive" />
    <link rel="shortcut icon" type="image/x-icon" href="/assets/img/raspcontrol/favicon.ico" />
    <link rel="icon" type="image/png" href="/assets/img/favicon.ico" />
    <!--[if lt IE 9]><script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" media="screen" />
    <link href="/assets/css/raspcontrol.css" rel="stylesheet" media="screen" />
  </head>

  <body>

		<div class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<a class="navbar-brand" href="<?= INDEX; ?>"><img src="/assets/img/raspcontrol/raspcontrol-icon.png" alt="rbpi" /> Raspcontrol - the Raspberry Pi Control Center</a>
				</div>
			</div>
		</div>
    
    <div id="content">
      <?php if (isset($_SESSION['message'])) { ?>
      <div class="container">
        <div class="alert alert-error">
          <strong>Oups!</strong> <?php echo $_SESSION['message']; ?>
        </div>
      </div>
      <?php unset($_SESSION['message']); } ?>

<div class="container details">

		<table>
          <tr id="check-system">
            <td class="check"><i class="icon-cog"></i> System</td>
            <td class="icon"></td>
            <td class="infos">
              hostname: <span class="text-info"><?php echo Rbpi::hostname(true); ?></span>
              <br />distribution: <span class="text-info"><?php echo Rbpi::distribution(); ?></span>
              <br />kernel: <?php echo Rbpi::kernel(); ?>
              <br />firmware: <?php echo Rbpi::firmware(); ?>
            </td>
          </tr>

          <tr id="check-uptime">
            <td class="check"><i class="icon-time"></i> Uptime</td>
            <td class="icon"></td>
            <td class="infos"><?php echo $uptime; ?></td>
          </tr>

          <tr id="check-ram">
            <td class="check"><i class="icon-asterisk"></i> RAM</td>
            <td class="icon"><?php echo icon_alert($ram['alert']); ?></td>
            <td class="infos">
			<div class="progress" id="popover-ram">
                <div class="progress-bar progress-bar-<?php echo $ram['alert']; ?>" style="width: <?php echo $ram['percentage']; ?>%;"><?php echo $ram['percentage']; ?>%</div>
            </div>
			<div id="popover-ram-head" class="hide">Top RAM eaters</div>
			<div id="popover-ram-body" class="hide"><?php echo shell_to_html_table_result($ram['detail']); ?></div>
              free: <span class="text-success"><?php echo $ram['free']; ?>Mb</span>  &middot; used: <span class="text-warning"><?php echo $ram['used']; ?>Mb</span> &middot; total: <?php echo $ram['total']; ?>Mb
			</td>
          </tr>

          <tr id="check-swap">
            <td class="check"><i class="icon-refresh"></i> Swap</td>
            <td class="icon"><?php echo icon_alert($swap['alert']); ?></td>
            <td class="infos">
              <div class="progress">
                <div class="progress-bar progress-bar-<?php echo $swap['alert']; ?>" style="width: <?php echo $swap['percentage']; ?>%;"><?php echo $swap['percentage']; ?>%</div>
              </div>
              free: <span class="text-success"><?php echo $swap['free']; ?>Mb</span>  &middot; used: <span class="text-warning"><?php echo $swap['used']; ?>Mb</span> &middot; total: <?php echo $swap['total']; ?>Mb
            </td>
          </tr>

          <tr id="check-cpu">
            <td class="check"><i class="icon-tasks"></i> CPU</td>
            <td class="icon"><?php echo icon_alert($cpu['alert']); ?></td>
            <td class="infos">
              loads: <?php echo $cpu['loads']; ?> [1 min] &middot; <?php echo $cpu['loads5']; ?> [5 min] &middot; <?php echo $cpu['loads15']; ?> [15 min]
              <br />running at <span class="text-info"><?php echo $cpu['current']; ?></span> (min: <?php echo $cpu['min']; ?>  &middot;  max: <?php echo $cpu['max']; ?>)
              <br />governor: <strong><?php echo $cpu['governor']; ?></strong>
            </td>
          </tr>

          <tr id="check-cpu-heat">
            <td class="check"><i class="icon-fire"></i> CPU</td>
            <td class="icon"><?php echo icon_alert($cpu_heat['alert']); ?></td>
            <td class="infos">
			<div class="progress" id="popover-cpu">
                <div class="progress-bar progress-bar-<?php echo $cpu_heat['alert']; ?>" style="width: <?php echo $cpu_heat['percentage']; ?>%;"><?php echo $cpu_heat['percentage']; ?>%</div>
            </div>
			<div id="popover-cpu-head" class="hide">Top CPU eaters</div>
			<div id="popover-cpu-body" class="hide"><?php echo shell_to_html_table_result($cpu_heat['detail']); ?></div>
              heat: <span class="text-info"><?php echo $cpu_heat['degrees']; ?>°C</span>
            </td>
          </tr>

          <tr class="storage" id="check-storage">
            <td class="check" rowspan="<?php echo sizeof($hdd); ?>"><i class="icon-hdd"></i> Storage</td>
            <?php
              for ($i=0; $i<sizeof($hdd); $i++) {
                echo '<td class="icon" style="padding-left: 10px;">', icon_alert($hdd[$i]['alert']), '</td>
            <td class="infos">
              <i class="icon-folder-open"></i> ', $hdd[$i]['name'] , '
              <div class="progress">
                <div class="progress-bar progress-bar-', $hdd[$i]['alert'], '" style="width: ', $hdd[$i]['percentage'], '%;">', $hdd[$i]['percentage'], '%</div>
              </div>
              free: <span class="text-success">', $hdd[$i]['free'], 'b</span> &middot; used: <span class="text-warning">', $hdd[$i]['used'], 'b</span> &middot; total: ', $hdd[$i]['total'], 'b &middot; format: ', $hdd[$i]['format'], '
            </td>
          </tr>
          ', ($i == sizeof($hdd)-1) ? null : '<tr class="storage">';
              }
            ?>

          <tr id="check-network">
            <td class="check"><i class="icon-globe"></i> Network</td>
            <td class="icon"><?php echo icon_alert($net_connections['alert']); ?></td>
            <td class="infos">
              IP: <span class="text-info"><?php echo Rbpi::internalIp(); ?></span> [internal] &middot;
              <span class="text-info"><?php echo Rbpi::externalIp(); ?></span> [external]
              <br />received: <strong><?php echo $net_eth['down']; ?>Mb</strong> &middot; sent: <strong><?php echo $net_eth['up']; ?>Mb</strong> &middot; total: <?php echo $net_eth['total']; ?>Mb
              <br />connections: <?php echo $net_connections['connections']; ?>
            </td>
          </tr>
           
          <?php
          if($temp['degrees'] != "N/A"){
          ?>
          <tr id="check-temp">
            <td class="check"><i class="icon-fire"></i> DS18B20</td>
            <td class="icon"><?php echo icon_alert($temp['alert']); ?></td>
            <td class="infos">
              <span class="text-info"><?php echo $temp['degrees']; ?></span>
            </td>
          </tr>
          <?php
          }
          ?>

        </table>
      </div>


    </div> <!-- /content -->
		
		<script src="/assets/js/jquery-2.1.3.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
		<script src="/assets/js/details.js"></script>
  </body>
</html>
