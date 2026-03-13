'use strict';

function NotReceivedLoansApp()
{
    const app = this;

    app.initSetManager = function () {
        $(document).on('click', '.js-set-manager', function () {
            let $this = $(this);
            let order_id = $(this).data('order');

            $.ajax({
                type: 'POST',
                data: {
                    action: 'set_manager',
                    order_id: order_id
                },
                success: function (resp) {
                    if (!!resp.error) {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });
                    } else {
                        $this.closest('.jsgrid-row').find('.js-not-received-loan-manager-name').text(resp.manager_name);
                    }
                }
            })
        });
    };

    let initSaveFieldForm = function () {
            $(document).on('change', '.field-to-save', function () {
                $(this).parents('form.common_save_order_field_form').submit();
            })

            $(document).on('submit', '.common_save_order_field_form', function (e) {
                e.preventDefault();

                let $form = $(this);
                let $fieldToSave = $form.find('.field-to-save');

                if ($fieldToSave.length === 0) {
                    return;
                }

                $.ajax({
                    url: $form.attr('action'),
                    data: {
                        action: 'save_select',
                        field: $fieldToSave.attr('name'),
                        value: $fieldToSave.val()
                    },
                    type: 'POST',
                    success: function (resp) {
                        if (resp.success) {
                            Swal.fire({
                                timer: 5000,
                                title: 'Изменения сохранены',
                                type: 'success'
                            });
                        } else {
                            Swal.fire({
                                text: resp.error,
                                type: 'error'
                            });
                        }
                    }
                })
            })
        }

    ;(function () {
        app.initSetManager();
        initSaveFieldForm();
    })();
}

$(function () {
    new NotReceivedLoansApp();
});

$(document).ready(function () {
    function getClientPageIds()
    {
        let ids = [];
        let cells = $('.stage-cell');

        for (let i = 0; i < cells.length; i++) {
            ids.push(cells.eq(i).data('order-id'));
        }

        return ids;
    }

    function updateClientLastCallData()
    {
        $.ajax({
            url: '/ajax/not_received_loans.php',
            method: 'post',
            dataType: 'json',
            data: {
                action: 'last_calls',
                userIds: getClientPageIds()
            },
            success: function (lastCalls) {
                for (let i = 0; i < lastCalls.length; i++) {
                    let orderId = lastCalls[i].user_id;
                    let callCell = $('td[data-order-id="' + orderId + '"].last-call-cell');

                    callCell.html(lastCalls[i].created);
                }
            }
        });
    }

    setInterval(updateClientLastCallData, 3000);
});