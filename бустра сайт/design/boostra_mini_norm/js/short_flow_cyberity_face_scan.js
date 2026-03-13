import cyberitySdk from '/js/cyberity/index.esm.js';

document.addEventListener('DOMContentLoaded', launchWebSdk)

async function launchWebSdk() {
    let response = await getAction('access_token');

    const preloader = document.querySelector('.preloader.preloader-show');

    if (preloader) {
        preloader.style.display = 'none';
    }

    let sdkInstance = cyberitySdk.init(
        response?.access_token ?? '',
        async () => {
            const response = await getAction('access_token');
            return response?.access_token ?? '';
        }
    )
        .withConf({
            lang: 'ru',
        })
         .on('idCheck.onApplicantLoaded', async (payload) => {
             console.log('onApplicantLoaded', payload)
             await getAction('started_application');
         })
        .on('idCheck.stepCompleted', async (payload) => {

            console.log('stepCompleted', payload)
            response = await getAction('added_selfie');
            console.log('stepCompleted added_selfie', response);
            die();
        })
        .on('idCheck.onApplicantSubmitted', async (payload) => {

            console.log('onApplicantSubmitted', payload)

            die();

        })
        .on('idCheck.onApplicantStatusChanged', async (payload) => {
            console.log('!!!!', payload);
            response = await getAction('added_selfie');
            console.log('onApplicantStatusChanged added_selfie', response);
            if ((response.redirect) && (response.status === 'rejected')) {
                location.reload();
                //window.location.href = response.redirect
            }
            if(response.verification.status === 'COMPLETED'){
                location.reload();
            }
        })

        .on('idCheck.onApplicantSubmitted', async (payload) => {
            console.log('!!!!', payload);
            console.log('isSelfi', payload?.levelName === 'selfy-kys-level')
            if (payload.reviewStatus === 'completed') {
                const faceCheckResponse = await getAction('face_check');
                console.log('face_check', faceCheckResponse);
            }
            const response = await getAction('added_selfie');
            console.log('added_selfie', response);

            location.reload();
        })

        .on('idCheck.onError', (error) => {
            console.log('onError', error)
        })

        .build();

    sdkInstance.launch('#self-validation-container');
}

async function getAction(action) {
    let callbackUrl = $('#callbackUrl').val();
    let applicantLevel = $('#applicantLevel').val();

    return await $.ajax({
        url: callbackUrl,
        data: {
            action: action,
            applicantLevel: applicantLevel,
        },
        success: function (response) {
            if (!response || !response.success) {
                const message = response.message || 'Неизвестная ошибка. Повторите попытку или обратитесь в техническую поддержку.'
                alert(message)

                return {
                    success: false,
                    message: message
                };
            }

            return response;
        },
        error: function (response) {
            console.log('error')
            console.log(response)
        }
    })
}