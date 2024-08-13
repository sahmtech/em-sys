function submittedDataFunc(response) {
    if (response.success) {

        var submittedData = response.client;
        var resultItem = response.selectedData;
        var quantity = response.quantity;
        var action = response.action;

        var newTotal2 = submittedData.monthly_cost_for_one * quantity;
        var newTotal = submittedData.monthly_cost_for_one;
        var translatedGender = genderTranslations[submittedData.gender];

        var newRow = '<tr class="product_row" data-client-id="' + submittedData.id + '">' +
            '<td class="text-center">' + response.profession + '</td>' +
            '<td class="text-center">' + response.nationality + '</td>' +
            '<td class="text-center">' + translatedGender + '</td>' +
            '<td class="text-center">' + quantity + '</td>' +
            '<td class="text-center total-column">' + newTotal + '</td>' +
            '<td class="text-center total-column2">' + newTotal2 + '</td>' +
            '<td class="text-center">' +
            '<i class="fas fa-pencil-alt edit-client" aria-hidden="true" data-client-id="' + submittedData.id + '" style="margin-right: 10px;"></i>' +
            '<i class="fas fa-times delete-client" aria-hidden="true" data-client-id="' + submittedData.id + '"></i>' +
            '</td>' +
            '</tr>';

        if (action === 'edit') {
            var $existingRow = $('#pos_table tbody').find('tr[data-client-id="' + submittedData.id + '"]');
            $existingRow.remove();
            var index = quantityArr.findIndex((quantity, i) => productIds[i] === submittedData.id);
            if (index > -1) {
                quantityArr.splice(index, 1);
                productIds.splice(index, 1);
                resultsArray.splice(index, 1);
            }
            $('#productData').val(JSON.stringify(resultsArray));
            $('#productIds').val(JSON.stringify(productIds));
            $('#quantityArr').val(JSON.stringify(quantityArr));

            updateSumOfQuantityArr();


        } else {
            var totalQuantity = parseInt($('.total_quantity').text());
            $('.total_quantity').text(totalQuantity + 1);

        }

        $('#pos_table tbody').append(newRow);
        $('.quick_add_client_modal').modal('hide');
        $('#quick_add_client_form')[0].reset();
        $('#selectedData').val('');
        $('#action').val('add');

        var productRowCount = parseInt($('#product_row_count').val());
        $('#product_row_count').val(productRowCount + 1);

        updatePriceTotal();

        updateArray(resultItem, submittedData.id, quantity);
    } else {
        alert('Error: ' + response.message);
    }

}

// Add this to get the CSRF token from the meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Event delegation to handle delete button click
$(document).on('click', '.delete-client', function() {
    var clientId = $(this).data('client-id');
    var $row = $(this).closest('tr');
    var quantityToRemove = parseInt($row.find('td:nth-child(4)').text());

    $.ajax({
        url: '/sale/client/delete',
        type: 'POST',
        data: {
            id: clientId,
            _token: csrfToken
        },
        success: function(response) {
            if (response.success) {
                $row.remove();
                updatePriceTotal();

                // Update total quantity
                var totalQuantity = parseInt($('.total_quantity').text());
                $('.total_quantity').text(totalQuantity - 1);

                // Remove the item from the quantity array and update
                var index = quantityArr.findIndex((quantity, i) => productIds[i] === clientId);
                if (index > -1) {
                    quantityArr.splice(index, 1);
                    productIds.splice(index, 1);
                    resultsArray.splice(index, 1);
                }
                $('#productData').val(JSON.stringify(resultsArray));
                $('#productIds').val(JSON.stringify(productIds));
                $('#quantityArr').val(JSON.stringify(quantityArr));

                updateSumOfQuantityArr(); // Update sum of quantity array
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while deleting the client.');
        }
    });
});

$(document).on('click', '.edit-client', function() {
    var clientId = $(this).data('client-id');

    $.ajax({
        url: '/sale/client/edit/' + clientId,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                // Populate the modal form with the client data
                $('#professionSearch').val(response.client.profession_id).trigger('change');
                $('#nationalitySearch').val(response.client.nationality_id).trigger('change');
                $('#gender').val(response.client.gender).trigger('change');
                $('#essentials_salary').val(response.client.service_price);
                $('#input_number').val(response.client.quantity);

                // Set the action to edit
                $('input[name="action"]').val('edit');
                $('input[name="client_id"]').val(clientId);

                // Clear existing rows in the salary table body
                $('#salary-table-body').empty();

                if (response.additional_allowances && response.additional_allowances.length > 0) {
                    response.additional_allowances.forEach(function(allowance) {
                        var newRow = '<tr>' +
                            '<td>' +
                            '<select name="salary_type[]" class="form-control width-60 pull-left" style="height:40px">' +
                            Object.keys(allowanceTypeTranslations).map(key => `<option value="${key}">${allowanceTypeTranslations[key]}</option>`).join('') +
                            '</select>' +
                            '</td>' +
                            '<td>' +
                            '<select name="type[]" class="form-control" style="height:40px">' +
                            Object.keys(typeTranslations).map(key => `<option value="${key}">${typeTranslations[key]}</option>`).join('') +
                            '</select>' +
                            '</td>' +
                            '<td>' +
                            '<input type="text" name="amount[]" class="form-control width-60 pull-left" placeholder="Amount" value="' + allowance.amount + '">' +
                            '</td>';

                        // If contract form is 'operating_fees', add a checkbox column
                        if ($('#contract_form').val() === 'operating_fees') {
                            newRow += '<td><input type="checkbox" name="include_salary[]" class="include-salary-checkbox" style="margin:auto; display:block;"' + (allowance.include_salary ? ' checked' : '') + '></td>';
                        }

                        newRow += '</tr>';
                        $('#salary-table-body').append(newRow);

                        // Set the selected value for the allowance type and payment type
                        $('#salary-table-body select[name="salary_type[]"]').last().val(allowance.type).trigger('change');
                        $('#salary-table-body select[name="type[]"]').last().val(allowance.payment_type).trigger('change');
                    });
                } else {
                    // Add an empty row with placeholders if there are no additional allowances
                    var emptyRow = '<tr>' +
                        '<td>' +
                        '<select name="salary_type[]" class="form-control width-60 pull-left" style="height:40px">' +
                        Object.keys(allowanceTypeTranslations).map(key => `<option value="${key}">${allowanceTypeTranslations[key]}</option>`).join('') +
                        '</select>' +
                        '</td>' +
                        '<td>' +
                        '<select name="type[]" class="form-control" style="height:40px">' +
                        Object.keys(typeTranslations).map(key => `<option value="${key}">${typeTranslations[key]}</option>`).join('') +
                        '</select>' +
                        '</td>' +
                        '<td>' +
                        '<input type="text" name="amount[]" class="form-control width-60 pull-left" placeholder="Amount" value="">' +
                        '</td>';

                    // Add the checkbox if 'operating_fees' is selected
                    if ($('#contract_form').val() === 'operating_fees') {
                        emptyRow += '<td><input type="checkbox" name="include_salary[]" class="include-salary-checkbox" style="margin:auto; display:block;"></td>';
                    }

                    emptyRow += '</tr>';
                    $('#salary-table-body').append(emptyRow);
                }

                updateMonthlyCostAndTotal();

                // Show the modal
                $('.quick_add_client_modal').modal('show');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while fetching the client data.');
        }
    });
});


function updateMonthlyCostAndTotal() {
    var essentialsSalary = parseFloat($('#essentials_salary').val()) || 0;
    var totalAllowances = 0;
    console.log('updateMonthlyCost');
    if ($('#contract_form').val() == 'operating_fees') {
        console.log('11111111111111111');
        $('#salary-table-body tr').each(function() {
            var includeSalary = $(this).find('.include-salary-checkbox').is(':checked');
            console.log(includeSalary);
            if (includeSalary) {
                var amount = parseFloat($(this).find('input[name="amount[]"]').val()) || 0;
                totalAllowances += amount;
            }
        });
    } else {
        $('input[name="amount[]"]').each(function() {
            var amount = parseFloat($(this).val()) || 0;
            totalAllowances += amount;
        });
    }
    var gosiAmount = essentialsSalary * 0.02 * 24;
    var vacationAmount = (essentialsSalary / 30) * 21 * 2;
    var endServiceAmount = essentialsSalary / 2 * 2;
    var administrativeAmount = 375;
    var gosiMonthlyAmount = gosiAmount / 24;
    var vacationMonthlyAmount = vacationAmount / 24;
    var endServiceMonthlyAmount = endServiceAmount / 24;
    var administrativeMonthlyAmount = administrativeAmount / 1;
    $('#gosiAmount').text(gosiAmount.toFixed(2));
    $('#vacationAmount').text(vacationAmount.toFixed(2));
    $('#endServiceAmount').text(endServiceAmount.toFixed(2));
    $('#administrativeAmount').text(administrativeAmount.toFixed(2));


    $('#gosiMonthlyAmount').text(gosiMonthlyAmount.toFixed(2));
    $('#vacationMonthlyAmount').text(vacationMonthlyAmount.toFixed(2));
    $('#endServiceMonthlyAmount').text(endServiceMonthlyAmount.toFixed(2));
    $('#administrativeMonthlyAmount').text(administrativeMonthlyAmount.toFixed(2));


    var additionalMonthlyCost = gosiMonthlyAmount + vacationMonthlyAmount + endServiceMonthlyAmount + administrativeMonthlyAmount;
    if ($('#contract_form').val() == 'operating_fees') {
        var additionalMonthlyCost = administrativeMonthlyAmount;
        var monthlyCost = totalAllowances + additionalMonthlyCost;
    } else {
        var monthlyCost = essentialsSalary + totalAllowances + additionalMonthlyCost;
    }
    $('#monthly_cost').val(monthlyCost.toFixed(2));

    var input_number = parseFloat($('#input_number').val()) || 0;
    var total = monthlyCost * input_number;
    $('#total').val(total.toFixed(2));
}

$(document).on('input', '#essentials_salary, input[name="amount[]"], #input_number', function() {
    updateMonthlyCostAndTotal();
});
$(document).on('change', '.include-salary-checkbox', function() {
    updateMonthlyCostAndTotal();
});

function updatePriceTotal() {
    var totalPrice = 0;
    $('.product_row').each(function() {
        totalPrice += parseFloat($(this).find('.total-column').text());
    });
    $('#price_total_input').val(totalPrice);
    $('.price_total').text(totalPrice);
    var totalMonthlyAmount = parseFloat($('#total_monthly_amount').text()) || 0;
    var priceTotal = parseFloat($('.price_total').text()) || 0;

    var totalSum = 0;

    if ($('#contract_form').val() == 'operating_fees') {
        totalSum = priceTotal;
    } else {
        totalSum = totalMonthlyAmount + priceTotal;
    }

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

function updateArray(resultsArrayItem, productIdsItem, quantity) {
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
    console.error(quantityArr);
    quantityArrDisplay = quantityArr.reduce(function(accumulator, currentValue) {
        return accumulator + parseInt(currentValue, 10);
    }, 0);
    console.error(quantityArrDisplay);
    $('#quantityArrDisplay').val(quantityArrDisplay);
    $('#quantityArrDisplay2').text(quantityArrDisplay);
}

function addRow() {
    var newRow = $('#salary-table-body tr:first').clone();
    newRow.find('input[name="amount[]"]').val(''); // Clear the amount input
    newRow.find('.include-salary-checkbox').remove(); // Remove any existing checkbox

    if ($('#contract_form').val() === 'operating_fees') {
        newRow.prepend(
            '<td><input type="checkbox" name="include_salary[]" class="include-salary-checkbox"></td>'
        );
    } else {
        newRow.prepend('<td></td>');
    }

    $('#salary-table-body').append(newRow);
}

$('#add-row').click(function() {
    addRow();
});