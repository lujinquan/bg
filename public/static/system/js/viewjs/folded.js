layui.use(['form','layer','jquery'], function(){
  var layer = layui.layer,
      $= layui.jquery,
      form = layui.form;
/* -------------门禁折叠 S----------------- */
	//监听折叠
	$("#firstpane").on('click','.menu_head .j-icon',function(){
	   if ($(this).parent().hasClass("current")) {
	        $(this).parent().removeClass("current").siblings("div.menu_body").slideUp("slow");
	    }
	    else {
	      $(this).parent().addClass("current").next("div.menu_body").slideToggle(300).siblings("div.menu_body").slideUp("slow");
	      $(this).parent().siblings().removeClass("current");
	    }
	});			
		 //楼栋全选	
	    form.on('checkbox(chooseAll)', function (data) {
			  var that = $(this);
			  var a = data.elem.checked;
	      $("input[name='guard[]'],input[name='floor[]']").each(function () {
				 if (a == true) {
					that.parent().next("div.menu_body").find(".list").prop('checked',true);
					form.render('checkbox');
				} else {
					that.parent().next("div.menu_body").find(".list").prop('checked',false);
					form.render('checkbox');
				}
				
	      });
	    });
		  //楼层全选
		  form.on('checkbox(secondAll)', function (data) {
		  		  var that = $(this);
		  		  var b = data.elem.checked;
				$("input[name='guard[]'],input[name='floor[]']").each(function () {
					 if (b == true) {
						that.parent().next().find(".list").prop('checked',true);
						that.parents(".menu_body").prev().find(".chooseAllMessage").prop('checked',true);
						form.render('checkbox');
					} else {
						that.parent().next().find(".list").prop('checked',false);
						//that.parents(".menu_body").prev().find(".chooseAllMessage").prop('checked',false);
						form.render('checkbox');
					}	
				});
				if  ($("input[name='floor[]']:checked").length>0){
						  that.parents(".menu_body ").prev().find(".chooseAllMessage").prop('checked',true);
						  form.render('checkbox');
					 }
				else
					  {
						  that.parents(".menu_body ").prev().find(".chooseAllMessage").prop('checked',false);
						  form.render('checkbox');
					  }
		  });
	    //判断层级勾选
		  form.on('checkbox(list)', function (data) {
			 var that = $(this);
			 var c = data.elem.checked;
			 if  ($("input[name='guard[]']:checked").length>0){
				  that.parents("dd").prev().find(".list").prop('checked',true);
				  form.render('checkbox');
			 }
		   else
			  {
				  that.parents("dd").prev().find(".list").prop('checked',false);
				  form.render('checkbox');
			  }
		   if  ($("input[name='floor[]']:checked").length>0){
					  that.parents(".menu_body ").prev().find(".chooseAllMessage").prop('checked',true);
					  form.render('checkbox');
				 }
		  else
			  {
				  that.parents(".menu_body ").prev().find(".chooseAllMessage").prop('checked',false);
				  form.render('checkbox');
			  }
		 });  
		 /* 门禁折叠 E*/
		 
 })