$(document).ready(function () { 
    $("input[name='phone']").inputmask("+7 (999) 999-99-99");

    $("#success-modal-trigger").trigger('click');

    $("#car-deposit-form").on('submit', () => {
        if (is_developer) {
            console.info('ym reachGoal pts_zaim');
        } else {
            ym(45594498, 'reachGoal' , 'pts_zaim')
        }
    })
})