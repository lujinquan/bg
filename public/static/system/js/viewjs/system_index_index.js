var myChart = echarts.init(document.getElementById('chart1'));
var myChart1 = echarts.init(document.getElementById('chart2'));
//工位使用情况
function workstation(){
var sum = 122;
var option = {
	title: {
		zlevel: 0,
		text: [
			'{total|总计\r\n}',
			'{value|' +sum+ '}',
			'{name| 个}'
		].join(''),
		top: 'center',
		left: '58%',
		textAlign: 'center',
		textStyle: {
			rich: {
				total: {
					color: '#4A4A4A',
					fontSize: 14,
					lineHeight: 14,
				},
				value: {
					color: '#007AFF',
					fontSize: 18,
					fontWeight: 'bold',
					lineHeight: 18,
				},
				name: {
					color: '#4A4A4A',
					fontSize: 12,
					lineHeight: 12,
				},
			},
		},
	},
	tooltip : { //提示框组件
		trigger: 'item', //触发类型(饼状图片就是用这个)
		formatter: "{a}<br/>{b} : {c} ({d}%)" //提示框浮层内容格式器
	},
	color:['#14C19F', '#F6B55A'],  //手动设置每个图例的颜色
	legend: {  //图例组件
		orient: 'vertical',  //布局  纵向布局 图例标记居文字的左边 vertical则反之
		x: 'left',   //图例显示在右边
		y: 'center',   //图例在垂直方向上面显示居中
		itemWidth: 15,  //图例标记的图形宽度
		itemHeight: 15, //图例标记的图形高度
		data: ['未使用', '已使用'],
		textStyle: {    //图例文字的样式
			color: '#4A4A4A',  //文字颜色
			fontSize: 14    //文字大小
		}
	},
	calculable : true,
	series: [ //系列列表
		{
			name: '工位使用情况',  //系列名称
			type: 'pie',   //类型 pie表示饼图
			center:['60%', '50%'], //设置饼的原心坐标 不设置就会默认在中心的位置
			radius : ['45%', '80%'],  //饼图的半径,第一项是内半径,第二项是外半径,内半径为0就是真的饼,不是环形
			itemStyle : {  //图形样式
				normal : { //normal 是图形在默认状态下的样式；emphasis 是图形在高亮状态下的样式，比如在鼠标悬浮或者图例联动高亮时。
					label : {  //饼图图形上的文本标签
						show : false  //平常不显示
					},
					labelLine: {     //标签的视觉引导线样式
						show: false  //平常不显示
					}
				},
				emphasis : {   //normal 是图形在默认状态下的样式；emphasis 是图形在高亮状态下的样式，比如在鼠标悬浮或者图例联动高亮时。
					label : {  //饼图图形上的文本标签
						show : false
					}
				}
			},
			data:[
				{value:40, name:'未使用'},
				{value:60, name:'已使用'},
			]
		}
	]
};
myChart.setOption(option);
}
function conferenceroom(){
//会议室使用情况
var sum = 5;
var option1 = {
	title: {
		zlevel: 0,
		text: [
			'{total|总计\r\n}',
			'{value|' +sum+ '}',
			'{name| 个}'
		].join(''),
		top: 'center',
		left: '58%',
		textAlign: 'center',
		textStyle: {
			rich: {
				total: {
					color: '#4A4A4A',
					fontSize: 14,
					lineHeight: 14,
				},
				value: {
					color: '#007AFF',
					fontSize: 18,
					fontWeight: 'bold',
					lineHeight: 18,
				},
				name: {
					color: '#4A4A4A',
					fontSize: 12,
					lineHeight: 12,
				},
			},
		},
	},
	tooltip : { //提示框组件
		trigger: 'item', //触发类型(饼状图片就是用这个)
		formatter: "{a}<br/>{b} : {c} ({d}%)" //提示框浮层内容格式器
	},
	color:['#14C19F', '#F96768', '#F6B55A', '#3698DC'],  //手动设置每个图例的颜色
	legend: {  //图例组件
		orient: 'vertical',  //布局  纵向布局 图例标记居文字的左边 vertical则反之
		x: 'left',   //图例显示在右边
		y: 'center',   //图例在垂直方向上面显示居中
		itemWidth: 15,  //图例标记的图形宽度
		itemHeight: 15, //图例标记的图形高度
		data: ['已使用', '已预约', '未预约', '取消'],
		textStyle: {    //图例文字的样式
			color: '#4A4A4A',  //文字颜色
			fontSize: 14    //文字大小
		}
	},
	calculable : true,
	series : [ //系列列表
		{
			name: '会议室使用情况',  //系列名称
			type: 'pie',   //类型 pie表示饼图
			center:['60%', '50%'], //设置饼的原心坐标 不设置就会默认在中心的位置
			radius : ['45%', '80%'],  //饼图的半径,第一项是内半径,第二项是外半径,内半径为0就是真的饼,不是环形
			itemStyle : {  //图形样式
				normal : { //normal 是图形在默认状态下的样式；emphasis 是图形在高亮状态下的样式，比如在鼠标悬浮或者图例联动高亮时。
					label : {  //饼图图形上的文本标签
						show : false  //平常不显示
					},
					labelLine : {     //标签的视觉引导线样式
						show : false  //平常不显示
					}
				},
				emphasis : {   //normal 是图形在默认状态下的样式；emphasis 是图形在高亮状态下的样式，比如在鼠标悬浮或者图例联动高亮时。
					label : {  //饼图图形上的文本标签
						show : false
					}
				}
			},
			data:[
				{value:1, name:'已使用'},
				{value:40, name:'已预约'},
				{value:55, name:'未预约'},
				{value:5, name:'取消'},
			]
		}
	]
};
myChart1.setOption(option1);
};
setTimeout(function (){
            window.onresize = function () {
		     myChart.resize();
		     myChart1.resize();
		   };
		 }, 200);