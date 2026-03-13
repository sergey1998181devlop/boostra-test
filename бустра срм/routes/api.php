<?php

use App\Core\Application\Facades\Router;
use App\Http\Controllers\AutodebitController;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommentRecordAnalysisController;
use App\Http\Controllers\ComplaintTicketController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SiteSettingsController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\TicketCompanyController;
use App\Http\Controllers\UsedeskController;
use App\Http\Controllers\UserDncController;
use App\Http\Controllers\UserFeedbackController;
use App\Http\Controllers\VoxController;
use App\Http\Controllers\VoxSiteDncController;
use App\Http\Controllers\CbRequestController;

Router::get('app/clients', [ClientController::class, 'index'], ['app.token.verify']);
Router::post('app/clients/identification', [ClientController::class, 'identification'], ['app.token.verify']);
Router::post('app/clients/incoming-call/:id', [ClientController::class, 'incomingCall'], ['app.token.verify']);
Router::post('app/clients/outgoing-call/', [ClientController::class, 'outgoingCall']);
Router::post('app/clients/feedback/:id/notify', [UserFeedbackController::class, 'notifyFeedback'], ['app.token.verify']);
Router::post('app/clients/:id/unblock', [ClientController::class, 'unblockAccount'], ['app.token.verify']);
Router::post('app/clients/:id/block', [ClientController::class, 'blockAccount'], ['app.token.verify']);
Router::get('app/clients/:id/temporary-unsubscribe-sms', [ClientController::class, 'temporaryUnsubscribeSms'], ['app.token.verify']);
Router::post('app/clients/dnc', [UserDncController::class, 'create'], ['app.token.verify']);
Router::post('app/clients/dnc/payment-date', [UserDncController::class, 'createByPaymentDate'], ['app.token.verify']);

Router::post('app/clients/fromtech-incoming-call', [ClientController::class, 'fromtechIncomingCall'], ['app.token.verify']);

Router::post('app/calls/log', [CallController::class, 'logCall'], ['app.token.verify']);

Router::post('app/comments/record-analysis', [CommentRecordAnalysisController::class, 'save'], ['app.token.verify']);
Router::post('app/comments/record-analysis/send', [CommentRecordAnalysisController::class, 'send'], ['app.token.verify']);

Router::post('app/orders/:id/credit-doctor/toggle', [OrderController::class, 'toggleCreditDoctor']);
Router::post('app/orders/:id/disable-additional-services', [OrderController::class, 'disableAdditionalServices'], ['app.token.verify']);
Router::post('app/orders/:id/toggle-autodebit-for-user-cards', [AutodebitController::class, 'toggleAutodebitForUserCards'], ['app.token.verify']);
Router::post('app/orders/:id/toggle-autodebit-for-user-sbp-accounts', [AutodebitController::class, 'toggleAutodebitForUserSbpAccounts'], ['app.token.verify']);
Router::post('app/orders/:id/toggle-autodebit', [AutodebitController::class, 'toggleAutodebitForUser'], ['app.token.verify']);
Router::post('app/orders/:id/disable-robot-calls', [OrderController::class, 'disableRobotCalls'], ['app.manager_or_token.auth']);
Router::post('app/orders/:id/enable-robot-calls', [OrderController::class, 'enableRobotCalls'], ['app.manager_or_token.auth']);
Router::post('app/orders/:contract/disable-additional-services-by-list', [OrderController::class, 'disableAdditionalServicesByList'], ['app.token.verify']);
Router::post('app/orders/switch-prolongation', [OrderController::class, 'switchProlongation'], ['app.token.verify']);
Router::post('app/orders/get-license-key', [OrderController::class, 'getExtraServiceLicenseKey'], ['app.token.verify']);
Router::post('app/orders/get-recompense-additional-services', [OrderController::class, 'getRecompenseAdditionalServices'], ['app.token.verify']);
Router::post('app/orders/get-user-additional-services', [OrderController::class, 'getUserAdditionalServices'], ['app.token.verify']);
Router::patch('app/orders/:id/sync-status-1c', [OrderController::class, 'syncStatus1C']);

Router::get('app/clients/:id/references', [ClientController::class, 'getReferences'], ['app.token.verify']);
Router::get('app/clients/reference', [ClientController::class, 'getReference'], ['app.token.verify']);

Router::get('app/documents/contract', [DocumentsController::class, 'getContract'], ['app.token.verify']);
Router::post('app/documents/fix-director-name', [DocumentsController::class, 'fixDirectorName'], ['app.token.verify']);

Router::post('app/usedesk/ticket/complaint', [UsedeskController::class, 'complaintTicket'], ['app.usedesk.complaint_ticket.secret']);
Router::post('app/usedesk/ticket/user', [UsedeskController::class, 'userTicket'], ['app.usedesk.user_ticket.secret']);
Router::post('app/usedesk/ticket/analysis', [UsedeskController::class, 'saveAnalysis'], ['app.token.verify']);

Router::get('app/check-fail-services', [VoxController::class, 'checkFailServices']);
Router::patch('app/robot-call-callback', [VoxController::class, 'updateRobotCall']);
Router::post('app/vox-calls/import-report', [VoxController::class, 'importCallsReport'], ['app.token.verify']);

Router::post('app/blacklist/check', [BlacklistController::class, 'check'], ['app.token.verify']);
Router::post('app/blacklist/add', [BlacklistController::class, 'add']);
Router::post('app/blacklist/delete', [BlacklistController::class, 'delete']);
Router::post('app/blacklist/toggle', [BlacklistController::class, 'toggleStatus']);

Router::get('app/tickets/companies', [TicketCompanyController::class, 'index']);
Router::post('app/tickets/companies', [TicketCompanyController::class, 'create']);
Router::put('app/tickets/companies/:id', [TicketCompanyController::class, 'update']);
Router::delete('app/tickets/companies/:id', [TicketCompanyController::class, 'delete']);
Router::patch('app/tickets/companies/:id/use-in-tickets', [TicketCompanyController::class, 'setUseInTickets']);

Router::post('app/sms/send', [SmsController::class, 'send'], ['app.token.verify']);
Router::post('app/sms/send-license-sms', [SmsController::class, 'sendLicenseSms'], ['app.token.verify']);

Router::get('app/site-settings', [SiteSettingsController::class, 'index']);
Router::put('app/site-settings/voximplant-ai', [SiteSettingsController::class, 'toggleVoximplantAI'], ['app.token.verify']);

Router::get('app/vox-site-dnc', [VoxSiteDncController::class, 'index'], ['app.manager.auth']);
Router::get('app/vox-site-dnc/:id', [VoxSiteDncController::class, 'show'], ['app.manager.auth']);
Router::post('app/vox-site-dnc', [VoxSiteDncController::class, 'store'], ['app.manager.auth']);
Router::put('app/vox-site-dnc/:id', [VoxSiteDncController::class, 'update'], ['app.manager.auth']);
Router::delete('app/vox-site-dnc/:id', [VoxSiteDncController::class, 'destroy'], ['app.manager.auth']);
# Tickets
Router::post('app/tickets/complaint', [ComplaintTicketController::class, 'create'], ['app.token.verify']);

# CB Requests (parser webhook)
Router::post('app/cb-requests', [CbRequestController::class, 'store'], ['app.token.verify']);