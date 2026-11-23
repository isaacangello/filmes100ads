<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Services\MinerStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmController extends Controller
{
    /**
     * Painel administrativo com os indicadores do sistema.
     */
    public function index(Request $request, MinerStatusService $minerStatus)
    {
        $userId = $request->user()->id;

        $totalFilmes = DB::table('posts')->count();
        $meusFilmes = DB::table('posts')->where('user_id', $userId)->count();
        $totalLinks = DB::table('urls')->count();
        $totalCategorias = Categoria::count();

        $ultimosFilmes = DB::table('posts')
            ->select('posts.id', 'posts.name', 'posts.ano', 'posts.created_at', 'users.name as autor')
            ->leftJoin('users', 'users.id', '=', 'posts.user_id')
            ->orderByDesc('posts.id')
            ->limit(6)
            ->get();

        $filmesPorCategoria = DB::table('categorias')
            ->leftJoin('categorias_posts', 'categorias_posts.categoria_id', '=', 'categorias.id')
            ->select('categorias.name', DB::raw('COUNT(categorias_posts.post_id) as total'))
            ->groupBy('categorias.id', 'categorias.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $minerStatusData = $minerStatus->getStatus();

        return view('adm.index', [
            'stats' => [
                'totalFilmes' => $totalFilmes,
                'meusFilmes' => $meusFilmes,
                'totalLinks' => $totalLinks,
                'totalCategorias' => $totalCategorias,
            ],
            'ultimosFilmes' => $ultimosFilmes,
            'filmesPorCategoria' => $filmesPorCategoria,
            'minerStatus' => $minerStatusData,
            'minerStatusLabel' => $minerStatus->getStatusLabel($minerStatusData),
            'minerStatusBadge' => $minerStatus->getStatusBadgeClass($minerStatusData),
            'minerStatusIcon' => $minerStatus->getStatusIcon($minerStatusData),
        ]);
    }
}
