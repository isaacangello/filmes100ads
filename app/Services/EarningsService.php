<?php

namespace App\Services;

use App\Models\MovieView;
use App\Models\Post;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;

/**
 * Service para calcular rendimentos baseados em visualizações de filmes.
 */
class EarningsService
{
    /** Valor ganho por visualização (em "mint"). */
    protected float $earningsPerView;

    public function __construct(float $earningsPerView = 0.01)
    {
        $this->earningsPerView = $earningsPerView;
    }

    /**
     * Registra uma visualização de filme.
     *
     * Evita duplicatas verificando se o mesmo IP já visualizou o mesmo filme
     * nos últimos 5 minutos.
     */
    public function trackView(int $postId, ?int $userId, string $ip): bool
    {
        $ipHash = hash('sha256', $ip);

        // Verificar duplicata recente (mesmo IP, mesmo filme, últimos 5 minutos)
        $exists = MovieView::where('post_id', $postId)
            ->where('ip_hash', $ipHash)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($exists) {
            return false;
        }

        MovieView::create([
            'post_id' => $postId,
            'user_id' => $userId,
            'ip_hash' => $ipHash,
        ]);

        return true;
    }

    /**
     * Retorna as estatísticas de rendimento de um usuário.
     *
     * @return array{
     *     views_total: int,
     *     views_today: int,
     *     views_this_month: int,
     *     earnings_total: float,
     *     earnings_today: float,
     *     earnings_this_month: float,
     *     withdrawals_count: int,
     *     withdrawals_pending: int
     * }
     */
    public function getStats(int $userId): array
    {
        $posts = Post::where('user_id', $userId)->pluck('id');

        $viewsTotal = MovieView::whereIn('post_id', $posts)->count();

        // whereBetween com timestamps em vez de whereDate/whereMonth para permitir uso de índice
        $todayStart = today()->startOfDay();
        $todayEnd = today()->endOfDay();
        $viewsToday = MovieView::whereIn('post_id', $posts)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $viewsThisMonth = MovieView::whereIn('post_id', $posts)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        $withdrawals = Withdrawal::where('user_id', $userId);
        $withdrawalsCount = $withdrawals->count();
        $withdrawalsPending = (clone $withdrawals)->where('status', 'pending')->count();

        return [
            'views_total' => $viewsTotal,
            'views_today' => $viewsToday,
            'views_this_month' => $viewsThisMonth,
            'earnings_total' => round($viewsTotal * $this->earningsPerView, 2),
            'earnings_today' => round($viewsToday * $this->earningsPerView, 2),
            'earnings_this_month' => round($viewsThisMonth * $this->earningsPerView, 2),
            'withdrawals_count' => $withdrawalsCount,
            'withdrawals_pending' => $withdrawalsPending,
        ];
    }

    /**
     * Calcula o ganho total de um usuário com base nas visualizações.
     */
    public function calculateEarnings(int $userId): float
    {
        $stats = $this->getStats($userId);
        return $stats['earnings_total'];
    }

    /**
     * Retorna o histórico de saques de um usuário.
     */
    public function getWithdrawalHistory(int $userId)
    {
        return Withdrawal::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Retorna as visualizações recentes de um usuário (últimos 30 dias).
     */
    public function getRecentViews(int $userId, int $limit = 50)
    {
        $posts = Post::where('user_id', $userId)->pluck('id');

        return MovieView::whereIn('post_id', $posts)
            ->where('created_at', '>=', now()->subDays(30))
            ->with('post')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Retorna o valor por visualização configurado.
     */
    public function getEarningsPerView(): float
    {
        return $this->earningsPerView;
    }
}
