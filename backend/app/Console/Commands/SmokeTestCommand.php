<?php

namespace App\Console\Commands;

use App\Models\BotConversation;
use App\Models\ChatChannel;
use App\Models\Customer;
use App\Models\EmailMessage;
use App\Models\GmailAccount;
use App\Models\Invoice;
use App\Models\KnowledgeArticle;
use App\Models\Lead;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\WhatsappContact;
use Illuminate\Console\Command;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as HttpKernelContract;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmokeTestCommand extends Command
{
    protected $signature = 'crm:smoke {--fast : Only check core pages} {--as= : Email or username of the user to run the check as} {--denied : Treat 403 responses as expected (invert the check)}';

    protected $description = 'Boot every CRM page as the super admin and report failures.';

    public function handle(): int
    {
        $admin = $this->option('as') !== null
            ? User::query()->where('email', $this->option('as'))->orWhere('username', $this->option('as'))->first()
            : User::query()->where('is_super_admin', true)->first();

        if ($admin === null) {
            $this->error('No super admin found. Run php artisan db:seed first.');

            return self::FAILURE;
        }

        Auth::login($admin);

        app()->instance(
            ExceptionHandler::class,
            new class(app()) extends Handler
            {
                public function render($request, \Throwable $e)
                {
                    $response = parent::render($request, $e);

                    if ($response->getStatusCode() < 500) {
                        return $response;
                    }

                    throw $e;
                }
            }
        );

        $uris = $this->uris();

        if ($this->option('fast')) {
            $uris = array_slice($uris, 0, 12);
        }

        $failures = [];
        $checked = 0;

        foreach ($uris as $uri) {
            $checked++;
            $status = $this->probe($uri, $message);

            $deniedExpected = (bool) $this->option('denied');

            $ok = $deniedExpected
                ? $status === 403
                : ($status >= 200 && $status < 400);

            if ($ok) {
                $this->line(sprintf('  <fg=green>%s</> %s', $status, $uri));

                continue;
            }

            $this->line(sprintf('  <fg=red>%s</> %s', $status, $uri));

            if (($message ?? '') !== '') {
                $this->line('     <fg=yellow>'.mb_substr((string) $message, 0, 500).'</>');
            }

            $failures[$uri] = $status;
        }

        $this->newLine();
        $this->info($checked.' pages checked, '.count($failures).' failures.');

        return $failures === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<int, string>
     */
    private function uris(): array
    {
        $uris = [
            '/', '/dashboard', '/dashboard-sales', '/dashboard-ecommerce', '/analytics', '/search?q=test',
            '/contacts-profile', '/contacts-profile-template', '/notifications',
            '/sales', '/sales/my', '/sales/create', '/sales/payments', '/sales/reports', '/sales/export',
            '/leads', '/leads/pipeline', '/leads/assigned', '/leads/create', '/leads/export',
            '/customers', '/products', '/products/create', '/products/export', '/orders', '/invoices',
            '/employees', '/employees/create', '/employees/export', '/departments', '/designations',
            '/attendance', '/attendance/my', '/attendance/report', '/leaves', '/leaves/my', '/holidays',
            '/payroll', '/payroll/my', '/payroll/reports', '/compensations', '/commission-rules',
            '/targets', '/targets/my', '/targets/create',
            '/chat', '/apps-chat',
            '/whatsapp', '/whatsapp/contacts', '/whatsapp/settings',
            '/email-inbox', '/email/compose', '/gmail/settings',
            '/knowledge', '/bot/conversations', '/bot/settings',
            '/reports', '/activity-logs', '/users', '/users/create', '/roles', '/permissions',
            '/settings', '/settings/theme', '/settings/integrations',
            '/apps-calendar', '/apps-todo', '/apps-file-manager',
            '/ui-general', '/form-elements', '/tables-basic',
        ];

        if ($sale = Sale::query()->first()) {
            $uris[] = '/sales/'.$sale->id;
            $uris[] = '/sales/'.$sale->id.'/edit';
        }

        if ($lead = Lead::query()->first()) {
            $uris[] = '/leads/'.$lead->id;
        }

        if ($product = Product::query()->first()) {
            $uris[] = '/products/'.$product->id;
            $uris[] = '/products/'.$product->id.'/edit';
        }

        if ($customer = Customer::query()->first()) {
            $uris[] = '/customers/'.$customer->id;
        }

        if ($invoice = Invoice::query()->first()) {
            $uris[] = '/invoices/'.$invoice->id;
            $uris[] = '/invoices/'.$invoice->id.'/pdf';
        }

        if ($employee = User::query()->whereNotNull('role_id')->where('is_super_admin', false)->first()) {
            $uris[] = '/employees/'.$employee->id;
            $uris[] = '/employees/'.$employee->id.'/edit';
        }

        if ($payroll = Payroll::query()->first()) {
            $uris[] = '/payroll/'.$payroll->id;
            $uris[] = '/payroll/'.$payroll->id.'/payslip';
        }

        if ($channel = ChatChannel::query()->first()) {
            $uris[] = '/chat/'.$channel->id;
        }

        if ($contact = WhatsappContact::query()->first()) {
            $uris[] = '/whatsapp/'.$contact->id;
        }

        if (($email = EmailMessage::query()->first()) !== null) {
            $uris[] = '/email-read/'.$email->id;
        }

        if ($article = KnowledgeArticle::query()->first()) {
            $uris[] = '/knowledge?category='.$article->category;
        }

        if ($conversation = BotConversation::query()->first()) {
            $uris[] = '/bot/conversations/'.$conversation->id;
        }

        if ($account = GmailAccount::query()->first()) {
            $uris[] = '/gmail/settings?account='.$account->id;
        }

        return $uris;
    }

    private function probe(string $uri, ?string &$message = null): int
    {
        $message = '';

        try {
            $request = Request::create($uri, 'GET');
            $request->setLaravelSession(app('session.store'));

            /** @var HttpKernelContract $kernel */
            $kernel = app(HttpKernelContract::class);
            $response = $kernel->handle($request);
            $status = $response->getStatusCode();

            if ($status >= 500) {
                $message = $this->extractMessage((string) $response->getContent());
            }

            $kernel->terminate($request, $response);

            return $status;
        } catch (\Throwable $e) {
            $message = $e->getMessage();

            return 500;
        }
    }

    private function extractMessage(string $html): string
    {
        if (preg_match('/<title>(.*?)<\/title>/s', $html, $matches)) {
            return trim(html_entity_decode($matches[1]));
        }

        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');

        return mb_substr($text, 0, 300);
    }
}
