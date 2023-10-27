var lastTable = '';
$(document).on("click",".join", function(){
	var parent = $(this).parent('div');
	var parent2 = parent.parent('div');
	$(this).after('Join<br>').remove();
	parent2.find("select:first").clone().appendTo($(parent));
	return false;
});

$(document).on("change", "select[name*=remote_table], select[name=channel]", function(){
	var selectObj = $(this);
	$($(this)).parent().find('div').remove();
	if($(this).attr('name') != 'channel')
	$($(this)).after("<div class='"+$(this).val()+"'><br><a href='javascript:' class='join'>+ Add Table Join</a><br></div>");
	//
	lastTable = selectObj.parent('div').parent('div').find('select').val();
	if(lastTable && $(this).attr('name') != 'channel') {
		var url = document.URL+"&channel="+$("select[name=channel]").val()+"&getcondition=1&changefields=1"+'&table1='+lastTable+'&table2='+selectObj.val();
		url = url.replace(/#/g,'');
		$("#loading").remove();
		$(this).after("<div id=loading>Loading...</div>");
		
		$("[name=sbt]").attr("disabled","disabled");
		$.post(url, $("#form1").serialize(), function(data){
			$("#loading").html(data);
			//$("#loading").remove();
			$("[name=sbt]").removeAttr("disabled");
		})
	} else if($(this).attr('name') == 'channel') {
		var url = document.URL+"&channel="+$("select[name=channel]").val()+"&changefields=1";
		url = url.replace(/#/g,'');
//				alert(url);
		$("#loading").remove();
		$(this).after("<div id=loading>Loading...</div>");
		
		if($(this).attr('name') != 'channel')
		lastTable = selectObj.val();
		
		$("[name=sbt]").attr("disabled","disabled");
		$.post(url, $("#form1").serialize(), function(data){
			$("#loading").html(data);
//			$("#loading").remove();
			$("[name=sbt]").removeAttr("disabled");
		})		
	} else {
		$("#loading").remove();
	}


})

$(document).ready(function() {
	$(".import_list_item").click(function() {
		var id = $(this).attr('id');
		$(this).closest('td').append('<div id="edit_import_'+id+'" class="edit_form"><input name="title" value="'+$(this).text()+'" class="edit_form"><input type="hidden" name="import_id" value="'+id+'"> <input type="button" class="submit edit_form" value="Save"></div>');
		$(this).hide();
		return false;
	});

	$(document).on('click', 'input.edit_form[type="button"]', function(){
		var url = baseUrl+"/update_import_item&import_id="+$('input[type=hidden][name="import_id"]').val()+'&title='+$('input.edit_form[name="title"]').val();
		$.get(url, function(data){
			$('body').append(data);
		})
	})
	
	$('form').on('submit', function(){
		$('.submit').attr('disabled','disabled');
		return true;
	})
	
	$('#add_cat_connection').on('click', function(){
		var prev = $(this).closest('tr').prev();
		prev.clone().insertAfter(prev).find("select option").removeAttr('selected');
		return false;
	})
	


	$('.delete_link').on('click', function(){
		if( ! confirm($(this).text()+' this?')) return false;

		var url = $(this).attr('href');
		$.get(url, function(data){
			$('body').append(data);
		})
		$(this).closest('tr').hide();
		return false;
	});
});