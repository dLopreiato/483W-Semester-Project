$(document).ready(function() {
    populateResults();
});

function populateResults() {
	
	var searchString = location.search.split('term=')[1];
	searchString = decodeURI(searchString);
	
    $.ajax({
        url: PROTOCOL + ROOT_DIRECTORY + '/api/Find.php',
        dataType: 'json',
		data: {term: searchString},
        success: function(data) {
			if( !$.isArray(data) || !data.length ){
				$('#noResults').show();
			}
			else{
				$.each(data, function (i, item) {
					var itemCost;
					$.ajax({
						url: PROTOCOL + ROOT_DIRECTORY + '/api/GetItemCost.php',
						dataType: 'json',
						data: {item_id: item.item_id},
						success: function(data) {
							addItem(item.item_id, item.image,  item.description, item.name, data);
						},
						error: function(xhr, ajaxOptions, thrownError) {
							var serverErrorInfo = JSON.parse(unescape(xhr.responseText));
							for (var key in serverErrorInfo) {
								displayGeneralUserError(serverErrorInfo[key]['userErrorText']);
								console.error('AJAX Error: ' + serverErrorInfo[key]['errorDescription'] + "\n" + thrownError);
							}
						}
					});
					
				});
			}
        },
        error: function(xhr, ajaxOptions, thrownError) {
            var serverErrorInfo = JSON.parse(unescape(xhr.responseText));
            for (var key in serverErrorInfo) {
				displayGeneralUserError(serverErrorInfo[key]['userErrorText']);
                console.error('AJAX Error: ' + serverErrorInfo[key]['errorDescription'] + "\n" + thrownError);
            }
        }
    });
}


function addItem(itemID, image, description, category, itemCost){
	
	var appendString = '<div class="col-md-3"><div class="thumbnail"><a href="product.html?id=' + itemID +  '"><div class = "product-image" data-content="View"><img src="';
	if (image.substring(0, 4) != "http"){
		appendString += 'img/';
	}
	appendString += image + '"></div></a><div class="caption"><h4>' + description + '</h4><p style ="float: left;">' + category  + '</p>';
	appendString += '<p style ="text-align: right;"><b>' + itemCost + '</b><p></div></div></div>'
	
	$('#search-results').append(appendString);
}

function displayGeneralUserError(textToDisplay) {
	var divText = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + textToDisplay + '</div>';
    $('#error-view').append(divText);
}