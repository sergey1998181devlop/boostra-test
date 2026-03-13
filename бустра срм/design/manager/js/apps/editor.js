const BlockEmbed = Quill.import('blots/block/embed')
const Block = Quill.import('blots/block')

var icons = Quill.import('ui/icons')
icons[
  'divider'
] = `<svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 600 66.67"><path d="M566.67,66.67H33.33A33.34,33.34,0,0,1,33.33,0H566.67a33.34,33.34,0,0,1,0,66.67Z"/></svg>`
icons[
  'clean'
] = `<svg fill="currentColor" viewBox="0 0 24 24" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M15.8698693,2.66881311 L20.838395,7.63733874 C21.7170746,8.5160184 21.7170746,9.9406396 20.838395,10.8193193 L12.1565953,19.4998034 L18.25448,19.5 C18.6341758,19.5 18.947971,19.7821539 18.9976334,20.1482294 L19.00448,20.25 C19.00448,20.6296958 18.7223262,20.943491 18.3562506,20.9931534 L18.25448,21 L9.84446231,21.0012505 C9.22825282,21.0348734 8.60085192,20.8163243 8.13013068,20.345603 L3.16160505,15.3770774 C2.28292539,14.4983977 2.28292539,13.0737765 3.16160505,12.1950969 L12.6878888,2.66881311 C13.5665685,1.79013346 14.9911897,1.79013346 15.8698693,2.66881311 Z M5.70859531,11.7678034 L4.22226522,13.255757 C3.929372,13.5486503 3.929372,14.023524 4.22226522,14.3164172 L9.19079085,19.2849428 C9.33723746,19.4313895 9.5291792,19.5046128 9.72112094,19.5046128 L9.75,19.5 L9.78849588,19.5015989 C9.95740385,19.4864544 10.1221581,19.4142357 10.251451,19.2849428 L11.7375953,17.7978034 L5.70859531,11.7678034 Z M13.748549,3.72947329 L6.76959531,10.7068034 L12.7985953,16.7368034 L19.7777348,9.75865909 C20.070628,9.46576587 20.070628,8.99089214 19.7777348,8.69799892 L14.8092091,3.72947329 C14.5163159,3.43658007 14.0414422,3.43658007 13.748549,3.72947329 Z"></path></svg>`
icons[
  'strike'
] = `<svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 15.52 6.11"><path d="M13.82,5.28a3.57,3.57,0,0,0,.74-.07c.23,0,.48-.12.73-.2v.84a4.55,4.55,0,0,1-1.58.26,2.53,2.53,0,0,1-2-.79A3,3,0,0,1,11,3.53h-.94a1.59,1.59,0,0,1,.18.79,1.54,1.54,0,0,1-.55,1.26A2.45,2.45,0,0,1,8.15,6H6V3.53H4.59L5.52,6h-1L3.9,4.36H1.62L1,6H0l.93-2.5H0v-1H1.31L2.24,0H3.3l.92,2.49H6V0H7.77A2.83,2.83,0,0,1,9.54.44a1.25,1.25,0,0,1,.55,1.13,1.37,1.37,0,0,1-.27.87l-.08.09H11a3.51,3.51,0,0,1,.31-1.1,2.4,2.4,0,0,1,1-1.06A2.94,2.94,0,0,1,13.83,0a3.71,3.71,0,0,1,1.69.38l-.35.81a4.47,4.47,0,0,0-.64-.25,2.35,2.35,0,0,0-.71-.11,1.61,1.61,0,0,0-1.31.59,2.22,2.22,0,0,0-.44,1.11H15v1H12.05a2.25,2.25,0,0,0,.44,1.19A1.62,1.62,0,0,0,13.82,5.28ZM2.44,2l-.18.54h1l-.22-.61c0-.11-.09-.28-.17-.51S2.79,1,2.77.9A9.23,9.23,0,0,1,2.44,2ZM7,.9V2.53H8.16a1.44,1.44,0,0,0,.64-.18.74.74,0,0,0,.28-.66.67.67,0,0,0-.3-.61,2,2,0,0,0-1-.18ZM3.64,3.54h0Zm5.28,0,0,0H7V5.21H8A1.58,1.58,0,0,0,8.93,5a.86.86,0,0,0,.31-.74A.79.79,0,0,0,8.92,3.55Z"/></svg>`

class DividerBlot extends BlockEmbed {
  static blotName = 'divider'
  static tagName = 'hr'
}

class BlockquoteBlot extends Block {
  static blotName = 'blockquote'
  static tagName = 'blockquote'
  static className = 'blockquote'
}

Quill.register(DividerBlot)
Quill.register(BlockquoteBlot)

function showModal() {
  $('#editor-file-modal').modal('show')
}

function closeModal() {
  $('#editor-file-modal').modal('hide')
}

function closeModalWithBtn() {
  const modalElement = $('#editor-file-modal')
  modalElement.find('.close').on('click', function (e) {
    modalElement.modal('hide')
  })
}

function insertImageUrl() {
  $('.custom-editor-insert-image-link').on('click', function () {
    const imageElement = document.getElementById('imageUrl')
    const imageUrl = imageElement.value

    if (imageUrl.trim() !== '') {
      quill.focus()
      const range = quill.getSelection(true)
      quill.insertText(range.index, '\n', Quill.sources.USER)
      quill.insertEmbed(range.index, 'image', imageUrl)
      $(imageElement).val('')
      closeModal()
    }
  })
}

function setFileNameToLabel() {
  const fileInput = $('#custom-editor-file-id')
  const label = fileInput.siblings('.custom-file-label')
  const originalText = label.attr('data-title')

  fileInput.on('change', function (e) {
    if (!e.target.files.length) {
      label.html(originalText)
      return
    }

    const file = e.target.files[0]
    label.text(file.name || originalText)
  })
}

// function setUploadedFile() {
//   $('.custom-editor-file-upload-btn').on('click', function (e) {
//     const currentBtn = $(this)
//     const fileInput = document.getElementById('custom-editor-file-id')
//     const file = fileInput.files[0]

//     if (file) {
//       const reader = new FileReader()
//       reader.onload = function (e) {
//         quill.focus()
//         const range = quill.getSelection(true)
//         quill.insertText(range.index, '\n', Quill.sources.USER)
//         quill.insertEmbed(range.index, 'image', e.target.result)
//         $(fileInput).val(null)
//         const label = currentBtn.closest('.input-group').find('[for="custom-editor-file-id"]')
//         const originalText = label.attr('data-title')
//         label.text(originalText)
//         closeModal()
//       }
//       reader.readAsDataURL(file)
//     }
//   })
// }

function uploadFile(url, file) {
  const formData = new FormData()
  formData.append('image', file)
  formData.append('key', 'article-image')

  return new Promise((resolve, reject) => {
    $.ajax({
      url,
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function (response) {
        if (!!response.error) {
          Swal.fire({
            text: response.error,
            type: 'error',
          })
        } else {
          resolve(response.url)
        }
      },
      error: function (xhr, status, error) {
        // Убираем анимацию
        Swal.fire({
          text: 'Ошибка при отправке файла',
          type: 'error',
        })
        reject(xhr.responseText)
      },
    })
  })
}

function setUploadedFile() {
  $('.custom-editor-file-upload-btn').on('click', async function (e) {
    const currentBtn = $(this)
    const fileInput = document.getElementById('custom-editor-file-id')
    const file = fileInput.files[0]

    if (file) {
      $('.custom-editor-spinner-wrap').addClass('visible')
      const fileUrl = await uploadFile('/ajax/upload_image.php', file)
      quill.focus()
      const range = quill.getSelection(true)
      quill.insertText(range.index, '\n', Quill.sources.USER)
      quill.insertEmbed(range.index, 'image', fileUrl)
      $(fileInput).val(null)
      $('.custom-editor-spinner-wrap').removeClass('visible')
      const label = currentBtn.closest('.input-group').find('[for="custom-editor-file-id"]')
      const originalText = label.attr('data-title')
      label.text(originalText)
      closeModal()
    }
  })
}

const quill = new Quill('#editor', {
  theme: 'snow',
  bounds: '.custom-editor-container',
  modules: {
    toolbar: {
      container: [
        [{ header: [1, 2, 3, 4, 5, 6, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ list: 'bullet' }, { list: 'ordered' }],
        ['blockquote'],
        [{ align: [] }],
        ['link'],
        ['image'],
        [{ color: [] }],
        ['divider'],
        ['clean'],
      ],
      handlers: {
        image: function () {
          showModal()
        },
        divider: function (value) {
          const range = quill.getSelection(false)
          quill.insertEmbed(range.index, 'divider', false, Quill.sources.USER)
          quill.setSelection(range.index + 1, Quill.sources.USER)
        },
      },
    },
  },
  placeholder: 'Введите текст здесь...',
})

quill.on('text-change', function () {
  const html = quill.getSemanticHTML()
  const textarea = $('.custom-editor-container').find('textarea[name="content"]')
  textarea.html(html)
})

setFileNameToLabel()
setUploadedFile()
insertImageUrl()
closeModalWithBtn()
