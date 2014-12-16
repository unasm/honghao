<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<title>下载测试</title>
	<meta name="viewport" content="width=device-width,initial-scale=1">
	
</head>
<body>
	<div id = "float">
		<p>请点击右上方或者右下方的按钮，选择在<strong>浏览器</strong>中打开</p>
	</div>
	<div id = "container">
		<?php foreach($output as $data):?>
		<div style = "width:100%">		
			<p>披露时间:<?php  echo $data['time']?></p>
			<p>股票代码:<?php  echo $data['code']?></p>
			<a href = "<?php echo $data['link']?>"><h3><?php echo $data['title']?></h3></a>
			<a href = "<?php echo $data['link']?>">
				<button style  = "padding:5px">
					点击下载
				</button>
			</a>
		</div>
		<?php endforeach?>
	</div>
</body>
<style type="text/css" media="screen">
	#float{
		background:rgba(0,0,0,0.6);	
		width:100%;
		color:rgb(80, 212, 80);
		font-size:1.2em;
		position:absolute;
		top:0;
		left:0;
	}
	#container{
		margin-top:60px;
	}
    #float strong{
        color:rgb(200, 255, 198);
        font-size:1.1em;
    }
</style>
</html>
