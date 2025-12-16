<div id="modal_success_result">
    <h5><b>{$title}</b></h5>
    <i class="bi bi-check-circle"></i>
    <p><small>{$description}</small></p>
</div>

{literal}
    <style>
        #modal_success_result {
            padding-top: 20px;
            display: flex;
            flex-flow: column;
            align-items: center;
        }

        #modal_success_result i {
            margin: 1rem 0 0;
            font-size: 5rem;
            color: green;
        }

        #modal_success_result p {
            line-height: 1;
            text-align: center;
            margin-top: 0;
        }
    </style>
{/literal}
