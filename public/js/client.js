function submittedDataFunc(response) {
  
    if (response.success) {
      
        var submittedData = response.client;
      
        var resultItem = response.selectedData;

        var quantity = response.quantity;

      //  $deleteUrl = route('service.delete', ['id' , submittedData.id]);
 
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
            //'<td class="text-center"><a href="'+ deleteUrl + '"><i class="fas fa-times btn btn-xs btn-danger delete_service_button" aria-hidden="true"></i></td>' +
            
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
        }

  
const resultsArray = [];
const productIds = [];
const quantityArr = [];

        
    


function updateArray(resultsArrayItem,productIdsItem,quantity) {

    
    resultsArray.push(resultsArrayItem);
    productIds.push(productIdsItem);
    quantityArr.push(quantity);
  
 

    $('#productData').val(JSON.stringify(resultsArray));
    $('#productIds').val(JSON.stringify(productIds));
    $('#quantityArr').val(JSON.stringify(quantityArr));

}
    


// $(document).on('click', 'button.delete_service_button', function () {
//     swal({
//        title: LANG.sure,
//        text: LANG.confirm_delete_service,
//        icon: "warning",
//        buttons: true,
//        dangerMode: true,
//    }).then((willDelete) => {
//        if (willDelete) {
//            var href = $(this).data('href');
//            $.ajax({
//                method: "DELETE",
//                url: href,
//                dataType: "json",
//                success: function (result) {
//                    if (result.success == true) {
//                        toastr.success(result.msg);
//                        buildings_table.ajax.reload();
//                    } else {
//                        toastr.error(result.msg);
//                    }
//                }
//            });
//        }
//    });
// });