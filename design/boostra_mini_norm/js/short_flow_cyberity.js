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
            // console.log('onApplicantLoaded', payload);
            await getAction('started_application');
        })
        .on('idCheck.onApplicantStatusChanged', async (payload) => {
            // console.log('onApplicantStatusChanged', payload);
            if (payload?.reviewStatus === 'completed') {
                sendMetric('reachGoal', 'identification');
                window.location.href = "/register";
            }
        })
        .on('idCheck.stepCompleted', async (payload) => {
            // console.log('stepCompleted', payload);
            if (payload?.step === 'IDENTITY') {
                await getAction('added_passport');
                sendMetric('reachGoal', 'registration_income_to_contact');
                sendMetric('reachGoal', 'register_contact_click_go_phone');
                sendMetric('reachGoal', 'etap-telephone');
                sendMetric('reachGoal', 'etap-pasport'); // Короткий флоу: добавление фото паспорта
            } else if (payload?.step === 'SELFIE') {
                await getAction('added_selfie');
                sendMetric('reachGoal', 'identification'); // Короткий флоу: добавление сканирования лица
                window.location.href = "/register";
            }
        })
        .on('idCheck.onError', (error) => {
            // console.log('onError', error);
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

            if (action === 'added_passport') {
                const passportHeader1 = document.querySelector('.self_verification h1.passport');
                const selfieHeader1 = document.querySelector('.self_verification h1.selfie');

                const passportHeader5 = document.querySelector('.self_verification h5.passport');
                const selfieHeader5 = document.querySelector('.self_verification h5.selfie');

                if (passportHeader1 && !passportHeader1.classList.contains('hidden')) {
                    passportHeader1.classList.add('hidden');
                    passportHeader5.classList.add('hidden');
                }

                if (selfieHeader1 && selfieHeader1.classList.contains('hidden')) {
                    selfieHeader1.classList.remove('hidden');
                    selfieHeader5.classList.remove('hidden');
                }
            }

            return response;
        },
        error: function (response) {
            console.log('error')
            console.log(response)
        }
    })
}