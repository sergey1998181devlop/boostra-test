// if (window.$ !== undefined) {
//     $(document).ready(function (){
//         function checkAndSendNotification() {
//             new Notification('У вас наступила просрочка по займу, срочно погасите её!');
//         }
//
//         // тут уведомляем о просрочке только если разрешение на уведомления дано и просрочка есть
//         if (window.Notification && Notification.permission === "granted" && window.debtInDays > 0) {
//             checkAndSendNotification();
//         }
//     })
// }