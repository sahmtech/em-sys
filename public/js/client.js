function submittedDataFunc(response) {
  
    if (response.success) {
                
        var submittedData = response.client;
      
        var resultItem = response.selectedData;

 
        var newTotal= submittedData.monthly_cost_for_one * submittedData.alert_quantity;
        var newRow = '<tr class="product_row">' +
            
            '<td class="text-center">' + submittedData.profession_id + '</td>' +
            '<td class="text-center">' + submittedData.specialization_id + '</td>' +
            '<td class="text-center">' + submittedData.nationality_id + '</td>' +
            '<td class="text-center">' + submittedData.gender + '</td>' +
            '<td class="text-center">' + submittedData.monthly_cost_for_one + '</td>' +
            '<td class="text-center">' + submittedData.alert_quantity + '</td>' +
       
            '<td class="text-center total-column">' + newTotal + '</td>' +
            '<td class="text-center"><i class="fas fa-times" aria-hidden="true"></i></td>' +
            // '<input type="hidden" id="selectedData" name="selectedData" value="' + resultItem + '"></input>' +
            // '<input type="hidden" name="productIds" value="' + submittedData.id + '"></input>' +
        '</tr>';

        $('#pos_table tbody').append(newRow);
        $('.quick_add_client_modal').modal('hide');
        $('#quick_add_client_form')[0].reset();
        var productRowCount = parseInt($('#product_row_count').val());
        $('#product_row_count').val(productRowCount + 1);

    
        var totalQuantity = parseInt($('.total_quantity').text());
        $('.total_quantity').text(totalQuantity + 1);

        updatePriceTotal();

        updateArray(resultItem,submittedData.id );

    } else {
    
        alert('Error: ' + response.message);
    }
    

}


   function updatePriceTotal() {
            var totalPrice = 0;
            $('.product_row').each(function () {
                totalPrice += parseFloat($(this).find('.total-column').text());
            });
            $('#price_total_input').val(totalPrice);
            $('.price_total').text(totalPrice);
        }

  
const resultsArray = [];
const productIds = [];
        
    


function updateArray(resultsArrayItem,productIdsItem) {


    resultsArray.push(resultsArrayItem);
    productIds.push(productIdsItem);
  
 

    $('#productData').val(JSON.stringify(resultsArray));
    $('#productIds').val(JSON.stringify(productIds));
}
    



