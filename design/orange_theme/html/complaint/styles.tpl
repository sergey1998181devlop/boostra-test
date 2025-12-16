{literal}
    <style>
        #complaint {
            width: 100%;
            max-width: 700px;
            min-height: 450px;
            background: #FFFFFF;
            border-radius: 15px;
            padding: 30px;
            margin: 40px auto;
            position: relative;
        }

        #complaint .btns {
            display: flex;
            justify-content: space-between;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        #complaint .header h4 {
            font-family: 'Manrope', sans-serif;
            font-weight: 600;
            font-size: 24px;
            line-height: 33px;
            color: #282735;
        }

        #complaint .close-btn {
            cursor: pointer;
        }

        #complaint .close-btn img {
            width: 16px;
            height: 16px;
        }

        /* Контент модального окна */
        #complaint .content-complaint {
            display: flex;
            flex-direction: column;
        }

        #complaint .complaint-content h2 {
            font-family: 'Manrope', sans-serif;
            font-weight: 600;
            font-size: 20px;
            margin-bottom: 10px;
        }

        #complaint .complaint-content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        #complaint .input-control {
            margin-bottom: 20px;
        }

        #complaint .input-control input,
        #complaint .input-control select,
        #complaint .input-control textarea {
            width: 100%;
            height: 45px;
            border: 2px solid #333;
            border-radius: 5px;
            padding: 0 15px;
            font-family: 'Manrope', sans-serif;
            font-size: 14px;
            line-height: 130%;
            color: #606060;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #complaint .input-control textarea {
            height: auto;
            min-height: 120px;
            resize: none;
            padding: 15px;
        }

        #complaint #count_message {
            right: 10px;
            font-size: 0.6em;
            position: relative;
            top: -30px;
            float: right;
        }

        #complaint .input-control input::placeholder,
        #complaint .input-control textarea::placeholder {
            color: #AEAEAE;
        }

        #complaint .input-control select {
            background: none;
        }

        #complaint .complaint_category {
            height: 45px;
        }

        #complaint .complaint_category option {
            padding-left: 20px;
        }

        /* Стили для файлов и сообщений об ошибках */
        #complaint #complaint_file_name {
            margin-left: 10px;
            color: green;
        }

        #complaint .has-error * {
            color: red;
            border-color: red;
        }

        /* Модальное окно для отправки подтверждения */
        #complaint_sended.modal_email_complaint_sended_modal {
            background-color: white;
            max-width: 600px;
            margin: auto;
            overflow: hidden;
            border-radius: 5px;
            padding: 1rem;
            min-height: 120px;
            display: flex;
            flex-direction: column;
            position: relative;
            box-sizing: border-box;
        }

        #complaint_sended .close-btn {
            position: absolute;
            right: 1rem;
            top: 1rem;
            cursor: pointer;
        }

        #complaint_sended h2 {
            margin: auto;
        }

        /* Медиазапросы для адаптивности */
        @media (max-width: 700px) {

            #complaint,
            #complaint_sended {
                width: 100%;
                padding: 15px;
            }

            #complaint .complaint-content-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .info-text {
                font-size: 1rem!important;
            }
        }

        .info-text {
            font-size: 0.8rem;
        }

        .required {
            color: red;
            font-weight: bold;
            font-size: 1em;
            margin-left: 2px;
        }

        #complaint .footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }

        #complaint .footer .btn-send {
            font-size: 1.2rem;
            padding: 15px 30px;
        }

        #complaint .footer #add_complaint_file {
            font-size: 1.2rem;
            padding: 15px 30px;
        }

        #complaint .bi-paperclip::before {
            transform: rotate(30deg);
            color: #1f2937;
        }

        #complaint #add_complaint_file {
            background-color: #ffffff;
            border: 1px solid #DDDCDC;
        }

        #complaint #complaint_file_list {
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 20px;
            flex-wrap: wrap;
        }

        #complaint #complaint_file_list li {
            display: flex;
            align-items: center;
        }

        .white-popup-block {
            background: #fff;
            border: 2px solid #222;
            padding: 20px 30px;
            border-radius: 10px;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .complaint_file {
            width: 200px;
            height: 150px;
            background-position: 50%;
            background-size: cover;
            position: relative;
            background-color: #eee;
            border-radius: 10px;
        }

        .complaint_file span {
            font-size: 10px;
            color: #000;
            background-color: rgba(255, 255, 255, .5);
            padding: 6px;
            border-radius: 100px;
            position: absolute;
            left: 10px;
            bottom: 10px;
            max-width: 180px;
            box-sizing: border-box;
            overflow: hidden;
            text-overflow: ellipsis;
            backdrop-filter: blur(10px);
        }

        .remove-complaint-file {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 20px;
            height: 20px;
            background-color: #fff;
            border-radius: 100px;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #00000038;
            text-decoration: none !important;
            transition: all .4s;
        }

        .remove-complaint-file i {
            display: flex;
        }

        .remove-complaint-file:hover {
            color: #fff;
            background-color: #dc2626;
        }

        #modal_complaint_sended {
            width: 600px;
            min-height: auto;
            background: #FFFFFF;
            border-radius: 15px;
            padding: 30px;
            max-width: 100%;
            margin: 0 auto;
        }

        .complaint_loader {
            position: absolute;
            background-color: rgba(255, 255, 255, .5);
            backdrop-filter: blur(7px);
            inset: 0;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        @keyframes loader {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .complaint_loader:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 60px;
            height: 60px;
            margin-top: -30px;
            margin-left: -30px;
            border: 4px solid transparent;
            border-top: 4px solid #000;
            border-radius: 100px;
            animation: loader 1s linear 0s infinite;
        }

        .complaint_loader.loading {
            opacity: 1;
            visibility: visible;
            pointer-events: all;
        }

        .complaint_loader #uploadProgressBar {
            position: absolute;
            bottom: 50px;
            left: 30px;
            right: 30px;
            height: 20px;
            width: auto;
            -webkit-appearance: none;
        }

        .complaint_loader #uploadProgressBar:before {
            content: attr(data-label);
            font-size: 0.8em;
            line-height: 20px;
            vertical-align: 0;
            position: absolute;
            left: 0;
            right: 0;
        }

        progress::-webkit-progress-bar {
            background-color: #a4a4a4;
            border-radius: 20px;
        }

        progress::-webkit-progress-value {
            background-color: #00c137;
            border-radius: 20px;
        }

        progress::-moz-progress-bar {
            background-color: #00c137;
            border-radius: 20px;
        }
        .button, button {
            display: inline-block;
            padding: .7rem 1.17rem;
            border: none;
            box-shadow: -5px 5px 1rem rgba(0, 0, 0, .2);
            border-radius: 1.17rem;
            background-color: #2c2b39;
            font-size: .9rem;
            line-height: 1;
            font-family: "Circle", OpenSans, Arial, sans-serif;
            color: #FFF;
            cursor: pointer;
            transition: background .35s;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
{/literal}