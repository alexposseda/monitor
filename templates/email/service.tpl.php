<?php
    if(!function_exists('normalizeInterval')){
        function normalizeInterval($inputSeconds){
            $secondsInAMinute = 60;
            $secondsInAnHour  = 60 * $secondsInAMinute;
            $secondsInADay    = 24 * $secondsInAnHour;
        
            // extract days
            $days = floor($inputSeconds / $secondsInADay);
        
            // extract hours
            $hourSeconds = $inputSeconds % $secondsInADay;
            $hours       = floor($hourSeconds / $secondsInAnHour);
        
            // extract minutes
            $minuteSeconds = $hourSeconds % $secondsInAnHour;
            $minutes       = floor($minuteSeconds / $secondsInAMinute) + 1;
        
            // extract the remaining seconds
            $remainingSeconds = $minuteSeconds % $secondsInAMinute;
            $seconds          = ceil($remainingSeconds);
        
            if($seconds < 10){
                $seconds = '0' . $seconds;
            }
            if($minutes != 0 AND $minutes < 10){
                $minutes = '0' . $minutes;
            }
            if($hours != 0 AND $hours < 10){
                $hours = '0' . $hours;
            }
        
            // return the final array
            $obj = [
                'd' => $days,
                'h' => $hours,
                'm' => $minutes,
                's' => $seconds,
            ];
        
            return $obj;
        }
    }
?>
<div class="service">
    <div class="header">
        <span class="title"><?= $title ?></span>
        <span class="status status-<?= $status ?>"><?= $status ?></span>
        <?php 
            if(isset($old_state['updated'])):
                $interval = normalizeInterval(time() - $old_state['updated']);
                ?>
            status was changed
            from <span class="status old-status status-<?= $old_state['status'] ?>"><?= $old_state['status'] ?></span>
            since <?= date('Y-m-d H:i:s', $old_state['updated']) ?>
            (
            <span class="interval">
                    <?= ($interval['d'] != 0) ? $interval['d'] . ' days' : '' ?>
                    <?= ($interval['h'] != 0) ? $interval['h'] . ' hours' : '' ?>
                    <?= ($interval['m'] != 0) ? $interval['m'] . ' minutes' : '' ?>
                    <?= $interval['s'] . ' seconds' ?>
            </span>
            )
        <?php endif; ?>
    </div>
    <div class="content">
        <p class="title">Details</p>
        <div class="details">
            <p><?= date('Y-m-d H:i:s') ?></p>
            <p><span class="url"><?= $params['host'] ?>:<?= $params['port'] ?><?= $params['route'] ?></span>
                <span class="method"><?= $params['method'] ?></span>
                <?php if(isset($params['ip'])): ?>
                    <span class="ip"><?= $params['ip'] ?></span>
                <?php endif; ?>
            </p>
        </div>
        <?php if(isset($params['data'])): ?>
            <ul class="data">
                <?php foreach($params['data'] as $k => $v): if($k == 'password') {$v = '***';}?>
                    <li><span class="key"><?= $k ?></span> = <span class="value"><?= $v ?></span></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if(!empty($error)): ?>
            <div class="message">
                <?php foreach($error as $msg):?>
                    <p><?= $msg ?></p>
                <?php endforeach;?>
            </div>
        <?php endif; ?>
    </div>
</div>