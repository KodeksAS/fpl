<html>

    <head>
        <title>FPL STATS</title>
        <meta name="viewport" content="width=device-width">
        <script
                src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
                integrity="sha256-4+XzXVhsDmqanXGHaHvgh1gMQKX40OUvDEBTu8JcmNs="
                crossorigin="anonymous"></script>
        <script src="js/highcharts/code/highcharts.js"></script>
        <script src="js/highcharts/code/modules/series-label.js"></script>
        <script src="js/highcharts/code/modules/exporting.js"></script>
        <script src="js/highcharts/code/modules/export-data.js"></script>
        <script src="js/highcharts/code/modules/accessibility.js"></script>
        <link rel="stylesheet" href="https://use.typekit.net/qji3xea.css">
        <style>
            html {
                font-size:  18px;
                font-family: acumin-pro, sans-serif;
            }

            body {
                background: #eee;
                margin: 1vw;
            }

            div.sort {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .player {
                margin-top: 2vw;
                width: 46vw;
                background: #fff;
                padding: 1vw;
            }

            .player span {
                display: block;
                border-top: 1px solid #eee;
                padding: 4px 0;
            }

            .player:not(.date)>*:first-child{
                display: flex;
                justify-content: space-between;
            }

            .player:not(.date)>*:first-child span{
                border: 0;
            }

            .points {
                display: flex !important;
                justify-content: space-between;
                align-items: center;
            }

            .points span {
                border: 0;
            }

            button {
                padding: 5px;
            }

            .wrapper {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
            }

            .date {
                width: 100% !important;
                margin-top: 0 !important;
            }

            @media (max-width: 767px){
                body {
                    margin: 4vw;
                }
                .player {
                    margin-top: 4vw;
                    width: 100%;
                    padding: 2vw;
                }
            }
        </style>

        <style type="text/css">
            .highcharts-figure, .highcharts-data-table table {
                min-width: 360px; 
                max-width: 100%;
                margin: 1em auto;
            }

            .highcharts-data-table table {
                font-family: Verdana, sans-serif;
                border-collapse: collapse;
                border: 1px solid #EBEBEB;
                margin: 10px auto;
                text-align: center;
                width: 100%;
                max-width: 500px;
            }
            .highcharts-data-table caption {
                padding: 1em 0;
                font-size: 1.2em;
                color: #555;
            }
            .highcharts-data-table th {
                font-weight: 600;
                padding: 0.5em;
            }
            .highcharts-data-table td, .highcharts-data-table th, .highcharts-data-table caption {
                padding: 0.5em;
            }
            .highcharts-data-table thead tr, .highcharts-data-table tr:nth-child(even) {
                background: #f8f8f8;
            }
            .highcharts-data-table tr:hover {
                background: #f1f7ff;
            }

        </style>
    </head>
    <body>

        <div class="wrapper">

            <?php

            $ids = ['3521106','4472855','5529775','5603194'];

            $players = 'https://fantasy.premierleague.com/api/bootstrap-static/';
            $players = file_get_contents($players);
            $players = json_decode($players);


            foreach($players->events as $event){
                if($event->is_next == true){

                    $date = date_create($event->deadline_time, new DateTimeZone('Europe/London'));
                    $date->setTimezone(new DateTimeZone('Europe/Stockholm'));
                    echo '<div class="player date"><strong>Neste deadline:</strong> ' . date_format($date, 'd/m/Y H:i:s').'</div>';

                }
            }


            foreach($ids as $id){
                $url = 'https://fantasy.premierleague.com/api/entry/'.$id.'/';

                $json = file_get_contents($url);
                $json = json_decode($json);
                $leagues = $json->leagues->classic;

                $next = $json->current_event + 1;

                $gameweek = 'https://fantasy.premierleague.com/api/entry/'.$id.'/event/'.$json->current_event.'/picks/';
                $gameweek = file_get_contents($gameweek);
                $gameweek = json_decode($gameweek);

                $live = 'https://fantasy.premierleague.com/api/event/'.$json->current_event.'/live/';
                $live = file_get_contents($live);
                $live = json_decode($live);

                $live_points = 0;
                
                foreach($gameweek->picks as $player){
                    
                    foreach($live->elements as $points){
                        if($points->id == $player->element && $player->position <= 11){
                            
                            if($player->is_captain){
                                $live_points += $points->stats->total_points * 2;
                            } else {
                                $live_points += $points->stats->total_points;
                            }
                            
                        }
                    }

                }
        
                $total = $json->summary_overall_points - $json->summary_event_points;
                $total += $live_points; 
                
                
                echo '<div class="player" data-gameweek="'.$json->summary_event_points.'" data-overall="'.$json->summary_overall_points.'">';
                echo '<strong><span>'.$json->name.'</span>';
                if($gameweek->active_chip){
                    echo '<span>'.$gameweek->active_chip.'</span>';
                }
                echo '<span>'.$json->summary_overall_points.'p</span>';
                echo '</strong>';


                echo '<span class="points"><span>GW'.$json->current_event.' poeng: <strong>';
                echo $json->summary_event_points;
                echo '</strong></span>';

                echo '<strong>';
                echo 'Live: '.$live_points.'p &rarr; '.$total.'p';
                echo '</strong>';
                echo '</span>';

                echo '<span>Kaptein: ';
                foreach($gameweek->picks as $player){

                    foreach($players->elements as $player_info){

                        if($player_info->id == $player->element && $player->is_captain == true){
                            echo $player_info->web_name . ' (' .$player_info->event_points .'p - GW'.$next.': '. $player_info->ep_next .'*)';
                        }
                    }
                }


                echo '</span>';

                echo '<span>Visekaptein: ';
                foreach($gameweek->picks as $player){

                    foreach($players->elements as $player_info){

                        if($player_info->id == $player->element && $player->is_vice_captain == true){
                            echo $player_info->web_name . ' (' .$player_info->event_points .'p - GW'.$next.': '. $player_info->ep_next .'*)';
                        }
                    }


                }

                echo '</span>';

                echo '<span>Total verdi: ';
                $pattern = '/(\d)[^\d]*$/';
                $replacement = '.${1}';
                echo preg_replace($pattern, $replacement, $json->last_deadline_value);
                echo '</span>';

                echo '<span>Transfers denne runden: ';
                echo $gameweek->entry_history->event_transfers;
                echo '</span>';

                echo '</div>';
            }

            ;?>



        </div>

        <div class="sort">
            <button class="sort">Sorter etter gameweek score</button>
            <p>* Estimert</p>
        </div>



        <figure class="highcharts-figure">
            <div id="container"></div>

            <script type="text/javascript">

                Highcharts.chart('container', {

                    title: {
                        text: 'Total points'
                    },

                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    },

                    plotOptions: {
                        series: {
                            label: {
                                connectorAllowed: false
                            },
                            pointStart: 1
                        }
                    },

                    series: [

                        <?php
                        foreach($ids as $id):?>
                        {
                            <?php
                            $url = 'https://fantasy.premierleague.com/api/entry/'.$id.'/';
                            $json = file_get_contents($url);
                            $json = json_decode($json);
                            $points = [];?>
                            name: '<?php echo $json->name;?>',
                            data: [<?php

                                for ($week = 0; $week <= $json->current_event; $week++){

                                    $gameweek = 'https://fantasy.premierleague.com/api/entry/'.$id.'/event/'.$week.'/picks/';
                                    $gameweek = file_get_contents($gameweek);
                                    $gameweek = json_decode($gameweek);

                                    $points[] = $gameweek->entry_history->total_points;

                                }
                                $shift = array_shift($points);
                                echo implode($points, ',');
                                ;?>]
                        },<?php endforeach;?>
                    ],

                    responsive: {
                        rules: [{
                            condition: {
                                maxWidth: 500
                            },
                            chartOptions: {
                                legend: {
                                    layout: 'horizontal',
                                    align: 'center',
                                    verticalAlign: 'bottom'
                                }
                            }
                        }]
                    }

                });
            </script>

        </figure>

        <figure class="highcharts-figure">
            <div id="container_gw"></div>

            <script type="text/javascript">

                Highcharts.chart('container_gw', {

                    title: {
                        text: 'Gameweek points'
                    },

                    legend: {
                        layout: 'horizontal',
                        align: 'center',
                        verticalAlign: 'bottom'
                    },

                    plotOptions: {
                        series: {
                            label: {
                                connectorAllowed: false
                            },
                            pointStart: 1
                        }
                    },

                    series: [

                        <?php
                        foreach($ids as $id):?>
                        {
                            <?php
                            $url = 'https://fantasy.premierleague.com/api/entry/'.$id.'/';
                            $json = file_get_contents($url);
                            $json = json_decode($json);
                            $points = [];?>
                            name: '<?php echo $json->name;?>',
                            data: [<?php

                                for ($week = 0; $week <= $json->current_event; $week++){

                                    $gameweek = 'https://fantasy.premierleague.com/api/entry/'.$id.'/event/'.$week.'/picks/';
                                    $gameweek = file_get_contents($gameweek);
                                    $gameweek = json_decode($gameweek);

                                    $points[] = $gameweek->entry_history->points;

                                }
                                $shift = array_shift($points);
                                echo implode($points, ',');
                                ;?>]
                        },<?php endforeach;?>
                    ],

                    responsive: {
                        rules: [{
                            condition: {
                                maxWidth: 500
                            },
                            chartOptions: {
                                legend: {
                                    layout: 'horizontal',
                                    align: 'center',
                                    verticalAlign: 'bottom'
                                }
                            }
                        }]
                    }

                });
            </script>

        </figure>

        <script>

            function total(){
                var $wrapper = $('.wrapper');

                $wrapper.find('.player').sort(function(b, a) {
                    return +a.dataset.overall - +b.dataset.overall;
                })
                    .appendTo($wrapper);
            }

            function gameweek(){
                var $wrapper = $('.wrapper');

                $wrapper.find('.player').sort(function(b, a) {
                    return +a.dataset.gameweek - +b.dataset.gameweek;
                })
                    .appendTo($wrapper);

            }

            total();

            $('button.sort').click(function(){
                $(this).toggleClass('active');
                if($(this).hasClass('active')){
                    $(this).text('Sorter etter total score');

                    gameweek();

                } else {
                    $(this).text('Sorter etter gameweek score');

                    total();

                }
            })
        </script>
    </body>
</html>