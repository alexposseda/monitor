<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Report</title>
    <style>
        .wrap{
        
        }
        .service{
            margin-bottom: 10px;
        }
        .title{
            font-weight: bold;
        }
        .header{
            margin-bottom: 10px;
        }
        .header>.title{
        
        }
        .content{
            font-size: 0.8em;
            background-color: #fbfbfb;
            border: 1px solid #d3d3d3;
            padding: 3px;
        }
        .content>.title{
        
        }
        .content p{
            margin: 0;
        }
        .interval{
            font-weight: bold;
        }
        .status{
            font-weight: bold;
            color: #0040ff;
        }
        .status-error{
            color: #d82000;
        }
        .status-success{
            color: #019c00;
        }
        .status-warning{
            color: #ffa500;
        }
        .old-status{
        
        }
        .details{
        
        }
        .url{
        
        }
        .method{
            font-weight: bold;
        }
        .ip{
        
        }
        .data{
            list-style: none;
            padding: 5px 5px;
            background: #eee;
            display: inline-block;
            margin: 10px 10px 0px 0;
            font-size: 0.9em;
            opacity: 0;
        }
        .data:hover{
            opacity: 1;
        }
        .data>.key{
        
        }
        .data>.value{

        }
        .message{
        
        }
    </style>
</head>
<body>
<div class="wrap">
    <?php foreach($messages as $message): ?>
        <?= $message ?>
    <?php endforeach; ?>
</div>
</body>
</html>
