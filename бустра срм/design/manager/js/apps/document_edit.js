let global = null
$('.btn-delete-for-user').click(function (e) {
    $('#delete-button-modal')
        .val($(this).val())
        .data('table', $(this).data('table'))
    if ($(this).data('table') == 3) {
        $('#delete-button-modal').data('zaim', $(this).data('zaim'))
        $('#delete-button-modal').data('type', $(this).data('type'))
    }
    global = $(this)
})

$('#delete-button-modal').click(function () {
    let val = $(this).val()
    let table = $(this).data('table')
    let zaim = $(this).data('zaim')
    let type = $(this).data('type')
    $.ajax({
        url: '/ajax/delete_for_user.php',
        type: 'post',
        data: {
            id: val,
            table: table,
            zaim: zaim,
            type: type
        },
        success: function () {
            $("#delete-modal").modal('hide');
            global.parent().parent().remove()

        },
    })
})


$('.btn-download-file').click(function () {
    let dataType = $(this).attr('data-type');
    let dataDoc = $(this).attr('data-doc');
    let dataUser = $(this).attr('data-user');
    let dataDocType = $(this).attr('data-doc-type');
    let dataOrder = $(this).attr('data-order');
    $.ajax({
        url: 'ajax/download.php',
        type: 'GET',
        data: {
            dataType: dataType,
            dataDoc: dataDoc,
            dataUser: dataUser,
            dataDocType: dataDocType,
            dataOrder: dataOrder
        },
        success: function (response) {
            if (response.url) {
                let a = document.createElement('a');
                a.href = response.url;
                a.target = '_blank';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                alert('Failed to generate the download link.');
            }
        },
        error: function () {
            alert('An error occurred while generating the download link.');
        }
    });
});


$('#uploadBtn').click(function () {
    $('#pdfFile').click();
});


$('#pdfFile').change(function () {
    var fileInput = $(this)[0];
    var file = fileInput.files[0];
    let user = $(this).data('user')
    let order = $(this).data('order')
    var formData = new FormData();
    formData.append('pdfFile', file);
    formData.append('user', user);
    formData.append('order', order);
    $.ajax({
        url: 'ajax/upload_pdf.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (!!response.error)
            {
                Swal.fire({
                    text: response.error,
                    type: 'error',
                });
            }
            else
            {
                location.reload()
            }
        },
        error: function(){
            // Убираем анимацию
            Swal.fire({
                text: "Ошибка при отправке файла",
                type: 'error',
            });
        },
    });
});

let fileName = null
$('.btn-replace').click(function () {
    $(this).parent().find('.replace-pdf-file').click()
    fileName = $.trim($(this).parent().parent().find('.file-name>a').text())
})

$('.replace-pdf-file').change(function () {
    var fileInput = $(this)[0];
    var file = fileInput.files[0];
    let user = $(this).data('user')
    let order = $(this).data('order')
    let uid = $(this).data('uid')
    let type = $(this).data('type')
    let docType = $(this).data('doctype')
    let zaim = $(this).data('zaim')
    var formData = new FormData();
    formData.append('pdfFile', file);
    formData.append('user', user);
    formData.append('order', order);
    formData.append('uid', uid);
    formData.append('type', type);
    formData.append('docType', docType);
    formData.append('zaim', zaim);
    formData.append('fileName', fileName);
    formData.append('name', file.name);

    $.ajax({
        url: 'ajax/replace_doc.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (!!response.error)
            {
                Swal.fire({
                    text: response.error,
                    type: 'error',
                });
            }
            else
            {
                location.reload()
            }
        },
    });
})

//
var selectedDocumentId = 0;
var $selectedDocumentShowBtn = null;
$('.btn-file-lk').click(function () {
  selectedDocumentId = $(this).attr('data-document-id');
  $selectedDocumentShowBtn = $(this)
  let visible = $(this).attr('data-client_visible');
  if(visible == 1){
    $('.modal-title-visible-need-hide').show();
    $('.modal-title-visible-need-show').hide();
  }else {
    $('.modal-title-visible-need-hide').hide();
    $('.modal-title-visible-need-show').show();
  }
});
$('#show-file-lk-button-modal').click(function () {
  $("#show-file-lk-modal").modal('hide');
  $selectedDocumentShowBtn.css("opacity", 0.5);
  $.ajax({
    url: '/ajax/order/update-document-data.php',
    type: 'get',
    data: {
      document_id: selectedDocumentId,
      client_visible: true
    },
    success: function (response) {
      let $el = $('.btn-file-lk-' + selectedDocumentId)
      $el.css("opacity", 1);
      $el.attr('data-client_visible',response.client_visible)
      $el.text(response.client_visible == 1 ? 'Непоказывать в ЛК' : 'Отобразить в ЛК')
    },
  });
})

