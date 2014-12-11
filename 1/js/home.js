/*************************************************************************
  > File Name :  ./js/home.js
  > Author  :      unasm
  > Mail :         jiamin1@staff.sina.com.cn
  > Last_Modified: 2014-07-31 19:33:46
 ************************************************************************/
function ajax(callback){
}
window.onload = function(){
	var base = '';
	$(content).delegate('.reply' , 'click' , function(event){
		var name = this.getAttribute('name');
		console.log(name);
		base = 'api/reply/send?id=' + name;
		drf.style.display = '';
		var tmp = drf.getElementsByTagName("textarea");
		tmp[0].focus();
	});
	$(content).delegate('.favor' ,'click' ,function(event){
		var xml = new XMLHttpRequest();
		if(xml){
			xml.onreadystatechange = function(){
				if(xml.readyState === 4 && xml.status === 200){
					var doc = xml.responseText;
					doc = JSON.parse(doc);
					if(doc.favorited_time){
						alert("收藏成功");
					}else{
						alert("收藏失败");
					}
				}
			}
			console.log($(this).attr('href'));
			xml.open('GET' , $(this).attr('href'));
			xml.send();
		}
		event.preventDefault();
	});
	butrep.onclick = function(e){
		e = e || window.e;
		e.preventDefault();
		var xhp = new XMLHttpRequest();
		if(xhp){
			if(!base){
				alert("choose your subject to reply");
				return false;
			}

			xhp.onreadystatechange = function(){
				console.log(xhp.readyState);
				if(xhp.readyState === 4  && xhp.status === 200){
					var doc = xhp.responseText;
					//console.log(doc);
					doc = JSON.parse(doc);
					if(doc.error){
						alert("评论失败:" + doc.error);
					}else{
						alert("评论成功");
					}
				}
			}	
			xhp.open("POST" , base , true);
			xhp.send(new FormData(document.forms.namedItem('replyForm')));
		}else{
			alert("chrome support only");
		}
		drf.style.display = 'none';
	}
	$("#replyForm .fa-times").click(function(){
		//$(drf).css("display" , "none");
		drf.style.display = 'none';

	})
	document.getElementById('status').onclick = function(event){
		$("#dp").slideToggle();
	}
	document.getElementById('fakebut').onclick = function(event){
		event = event || window.event;
		var status = $.trim($("textarea[name = 'status']").val());
		var pic = $("input[type = 'file']").val();
		if(status && pic){
			var xml = new XMLHttpRequest();
			xml.onreadystatechange = function(e){
				if(xml.readyState === 4 && xml.status === 200){
					var doc = xml.responseText;
					console.log(doc);
					doc = JSON.parse(doc);
					alert(doc);
				}
			};
			xml.open("POST",dp.getAttribute('action'));
			xml.send(new FormData(document.forms.namedItem('dp')));
		}else if(!status){
			//console.log("dfasdf");
			$("textarea[name = 'status']").focus();
		}
		event.preventDefault();
	}
	document.getElementById('toUpload').onclick = function(event){
		event = event || window.event;
		var files = document.getElementById('dp').getElementsByTagName('input');
		console.log(files);
		for (var i = 0 ,len = files.length; i < len; i++) {
			if(files[i].getAttribute('type') == 'file'){
				files[i].click();
			}
		};
		event.preventDefault();
	}
}

