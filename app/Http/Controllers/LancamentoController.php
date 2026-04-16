<?php

namespace App\Http\Controllers;

use App\Contracts\LancamentoRepository;
use App\Mail\LancamentoNotificadoMail;
use App\Models\Lancamento;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LancamentoController extends Controller
{
    private const TIPOS = ['RECEITA', 'DESPESA'];

    private const SITUACOES = ['PAGO', 'PENDENTE', 'RECEBIDO'];

    public function __construct(
        private LancamentoRepository $lancamentos,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);
        $lancamentos = $this->lancamentos->all($filters);

        [$totalReceitas, $totalDespesas] = $this->calculateTotals($lancamentos);

        return view('lancamentos.index', [
            'lancamentos' => $lancamentos,
            'totalReceitas' => $totalReceitas,
            'totalDespesas' => $totalDespesas,
            'saldo' => $totalReceitas - $totalDespesas,
            'filters' => $filters,
            'tipos' => self::TIPOS,
            'situacoes' => self::SITUACOES,
        ]);
    }

    public function create(): View
    {
        return view('lancamentos.create', $this->formViewData(new Lancamento(), 'Novo lancamento'));
    }

    public function store(Request $request): RedirectResponse
    {
        $lancamento = $this->lancamentos->create($this->validateLancamento($request));

        $this->notifyLancamentoChange($lancamento, 'criado');

        return redirect()
            ->route('lancamentos.index')
            ->with('status', 'Lancamento criado com sucesso.');
    }

    public function edit(Lancamento $lancamento): View
    {
        return view('lancamentos.edit', $this->formViewData($lancamento, 'Editar lancamento'));
    }

    public function update(Request $request, Lancamento $lancamento): RedirectResponse
    {
        $lancamento = $this->lancamentos->update($lancamento, $this->validateLancamento($request));

        $this->notifyLancamentoChange($lancamento, 'atualizado');

        return redirect()
            ->route('lancamentos.index')
            ->with('status', 'Lancamento atualizado com sucesso.');
    }

    public function exportPdf(Request $request): Response
    {
        $filters = $this->validateFilters($request);
        $lancamentos = $this->lancamentos->all($filters);

        [$totalReceitas, $totalDespesas] = $this->calculateTotals($lancamentos);

        $html = view('lancamentos.pdf', [
            'lancamentos' => $lancamentos,
            'totalReceitas' => $totalReceitas,
            'totalDespesas' => $totalDespesas,
            'saldo' => $totalReceitas - $totalDespesas,
            'filters' => $filters,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="lancamentos.pdf"');
    }

    private function formViewData(Lancamento $lancamento, string $title): array
    {
        return [
            'lancamento' => $lancamento,
            'pageTitle' => $title,
            'formAction' => $lancamento->exists
                ? route('lancamentos.update', $lancamento)
                : route('lancamentos.store'),
            'formMethod' => $lancamento->exists ? 'PUT' : 'POST',
            'tipos' => self::TIPOS,
            'situacoes' => self::SITUACOES,
        ];
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'situacao' => ['nullable', 'string', 'in:' . implode(',', self::SITUACOES)],
        ]);
    }

    private function validateLancamento(Request $request): array
    {
        return $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'data_lancamento' => ['required', 'date'],
            'valor' => ['required', 'numeric', 'min:0'],
            'tipo_lancamento' => ['required', 'string', 'in:' . implode(',', self::TIPOS)],
            'situacao' => ['required', 'string', 'in:' . implode(',', self::SITUACOES)],
        ]);
    }

    private function calculateTotals(Collection $lancamentos): array
    {
        $totalReceitas = $lancamentos->where('tipo_lancamento', 'RECEITA')->sum('valor');
        $totalDespesas = $lancamentos->where('tipo_lancamento', 'DESPESA')->sum('valor');

        return [$totalReceitas, $totalDespesas];
    }

    private function notifyLancamentoChange(Lancamento $lancamento, string $acao): void
    {
        $recipient = config('finance.notification_email');

        if ($recipient) {
            Mail::to($recipient)->send(new LancamentoNotificadoMail($lancamento, $acao));
        }
    }
}
