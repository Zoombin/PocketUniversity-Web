$(function(){
	var F="";
	var H=$("#submit_ok");
	var E=$("#Money");
	var G=$("#otherMoney");
	var A=function(J,K){
		var I=function(){
			$("#btnReSelect","#pageDialog").bind("click",function(){
				ui.box.close()
			})
		};
		ui.box.show($("#payAltBox").html(),{title:'充值提醒',closeable:true});
	};
	var C=function(I){
		$("#hidPayName").val(F);
		$("#hidMoney").val(I);
		A()
	};
	$("#ten").click(function(){
		E.html("10");
		G.val("")
	});
	$("#fifty").click(function(){
		E.html("50");
		G.val("")
	});
	$("#hundred").click(function(){
		E.html("100");
		G.val("")
	});
	$("#twohundred").click(function(){
		E.html("200");
		G.val("")
	});
	$("#other").click(function(){
		E.html("0")
	});
	var B=function(){
		var I=G.val();
		if(I==""){
			E.html(0)
		}
		else{
                    E.html(I)
//			if(isNaN(parseInt(I))||I=="0"){
//				if(E.html()=="0"){
//					G.val("")
//				}
//				else{
//					G.val(E.html())
//				}
//			}
//			else{
//				E.html(parseInt(I))
//			}
		}
	};
	G.focus(function(){
		$("#other").attr("checked","checked");
		B()
	}).bind("keyup",function(J){
		B();
		var I=(window.event)?event.keyCode:J.keyCode;
		if(I==13){
			H.focus()
		}
	});
	var D=false;
	H.click(function(){
		var I=E.html();
		if(isNaN(parseInt(I))||I=="0"){
			alert("请选择或输入充值金额!");
			return false
		}
		$("input[name=account]").each(function(J,K){
			//console.log($(K).attr("checked"));
			if($(K).attr("checked")==true){
				D=true;
				F=$(K).attr("id")
			}
		});
		if(D==false){
			alert("请选择支付方式");
			return false
		}
		C(E.html());
		return F!=""
	})
})