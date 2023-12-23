function submittedDataFunc(response) {
  
    if (response.success) {
      
        var submittedData = response.client;
      
        var resultItem = response.selectedData;

        var quantity = response.quantity;

     
 
        var newTotal= submittedData.monthly_cost_for_one * quantity;
        var newRow = '<tr class="product_row">' +
            
            '<td class="text-center">' + response.profession + '</td>' +
            '<td class="text-center">' + response.specialization + '</td>' +
            '<td class="text-center">' + response.nationality + '</td>' +
            '<td class="text-center">' + submittedData.gender + '</td>' +
            
            '<td class="text-center">' + submittedData.monthly_cost_for_one + '</td>' +
            '<td class="text-center">' + quantity + '</td>' +
       
            '<td class="text-center total-column">' + newTotal + '</td>' +
            '<td class="text-center"><i class="fas fa-times" aria-hidden="true"></i></td>' +
       
            
        '</tr>';

        $('#pos_table tbody').append(newRow);
        $('.quick_add_client_modal').modal('hide');
        $('#quick_add_client_form')[0].reset();
        var productRowCount = parseInt($('#product_row_count').val());
        $('#product_row_count').val(productRowCount + 1);

    
        var totalQuantity = parseInt($('.total_quantity').text());
        $('.total_quantity').text(totalQuantity + 1);

        updatePriceTotal();

        updateArray(resultItem,submittedData.id,quantity);

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
        var totalMonthlyAmount = parseFloat($('#total_monthly_amount').text()) || 0;
        var priceTotal = parseFloat($('.price_total').text()) || 0;

        var totalSum = totalMonthlyAmount + priceTotal ;

       
        $('#total_sum_value').text(totalSum.toFixed(2));
        var totalSum = parseFloat($('#total_sum_value').text()) || 0;
        var fees = parseFloat($('#fees_input').val()) || 0;

        totalAmountWithFees = totalSum + fees;
   
     
        $('#total_amount_with_fees').text(totalAmountWithFees.toFixed(2));
    }

  
const resultsArray = [];
const productIds = [];
const quantityArr = [];
var quantityArrDisplay = 0;
    


function updateArray(resultsArrayItem,productIdsItem,quantity) {

 
    resultsArray.push(resultsArrayItem);
    productIds.push(productIdsItem);
    quantityArr.push(quantity);
  
   
    $('#productData').val(JSON.stringify(resultsArray));
    $('#productIds').val(JSON.stringify(productIds));
    $('#quantityArr').val(JSON.stringify(quantityArr));
   
    updateSumOfQuantityArr();
    

}
function updateSumOfQuantityArr() {
    var quantityArr = JSON.parse($('#quantityArr').val());
  
    quantityArrDisplay = quantityArr.reduce(function (accumulator, currentValue) {
        return accumulator + parseInt(currentValue, 10);
    }, 0);
   
    $('#quantityArrDisplay').val(quantityArrDisplay);
    $('#quantityArrDisplay2').text(quantityArrDisplay);
}
    


