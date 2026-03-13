import cyberitySdk from '/js/cyberity/index.esm.js';

document.addEventListener('DOMContentLoaded', launchWebSdk)
async function launchWebSdk() {
    const response = await selfValidation('access_token');

    const preloader = document.querySelector('.preloader.preloader-show');

    if (preloader) {
        preloader.style.display = 'none';
    }

    let sdkInstance = cyberitySdk.init(
        response.access_token ?? '',
        async () => {
            const response = await selfValidation('access_token');
            return response.access_token ?? '';
        }
    )
        .withConf({
            lang: 'ru',
            // phone: response.phone ?? '',
            // email: response.email ?? ''
        })
        .on('idCheck.onApplicantLoaded', async (payload) => {
            await selfValidation('started_application');
            // console.log('new_application', payload)
        })
        .on('idCheck.stepCompleted', async (payload) => {
            if (payload?.step === 'IDENTITY') {
                await selfValidation('added_passport');
            } else if (payload?.step === 'SELFIE') {
                await selfValidation('added_selfie');
            }

            // console.log('stepCompleted', payload)
        })
        .on('idCheck.onApplicantStatusChanged', async (payload) => {
            // console.log('onApplicantStatusChanged', payload)

            if (payload?.reviewStatus === 'pending') {
                const response = await selfValidation('check_files');
                if (response.success) {
                    location.reload();
                }
            }
        })
        .on('idCheck.onError', (error) => {
            // console.log('onError', error)
        })

        .build();

    sdkInstance.launch('#self-validation-container');
}

async function selfValidation(action) {
    return await $.ajax({
        url: 'ajax/self_verification.php',
        data: {
            action: action,
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
        }
    })
}