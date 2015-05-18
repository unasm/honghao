/*************************************************************************
 * File Name :  ./svg/xueqiu.js
 * Author  :      unasm
 * Mail :         unasm@sina.cn
 * Last_Modified: 2015-05-12 11:02:28
 ************************************************************************/
function getSvgOpt(data){
	var series = [];
	series.push({
		name: 'SZ102230',
		marker: {
			symbol: 'square'
		},
		data: data
	});
	series.push({
		name: 'SZ0', marker: { symbol: 'square'},
		data: [7.0, 6.9, 9.5, 14.5, 14.2, 15.5, 15.2,16.5,13.3, 18.3, 13.9, 9.6]
	});
	var opts = {
		chart: { type: 'spline',height:200 },
		title: { text: '' },
		//xAxis: { categories:cate },
		yAxis: {
			title: { text: '价格' },
			labels: { formatter: function () {return '￥' + this.value;} }
		},
		legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 5,
				},
		//控制竖线的
		tooltip: {  
					 shared: true ,
					 formatter: function () {
						 var s = 'time : ' + this.x  ;
						 $.each(this.points, function () {
							 s += '<br/>' + this.series.name + ': ' + this.y + 'm';
						 });
						 return s;
					 },
				 },
		series:series
	}
	return opts;
	//$(this).highcharts(opts);
}


function freshCurrent() {
	$.ajax({
		dataType:'json',
		url:window.current,
		success:function(data){
			console.log(data);
			$.each($("#data .current"), function(idx, value){
				console.log(value);
				var symbol = $(value).data('symbol');
				if (data[symbol]) {
					$(value).html(data[symbol]['current']);
					//$(value).html(1);
				}
			})
		},
		error : function (data) {
			console.log(data);		
		}
	},'json');
	$.getJSON(window.current,function(data){
	
	});
}
$(document).ready(function (){
	$("#data").delegate(".oper",'click', function(){
		var node = $(this.parentNode.parentNode).find('.svg')[0];
		if($(node).data('show') == 1) {
			$(node).slideUp();
			$(node).data('show', 0);
		} else {
			var data = [];
			for(var i = 5;i < 12;i++) {
				data.push({x:i,y:i * 2})	;
			}
			for(var i = 13;i < 29;i+=2) {
				data.push({x:i,y:i * 2})	;
			}
			$(node).highcharts( getSvgOpt(data ));
			$(node).slideDown();	
			$(node).data('show', 1);
		}
	})
	//freshCurrent
	setInterval(freshCurrent,60000);
})

