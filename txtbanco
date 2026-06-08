<?php
/**
 * Componente Unificado de Boletim
 * Renderiza boletins com design consistente para alunos e professores
 * Suporta diferentes tipos de períodos (Bimestre/Trimestre/Semestre)
 */

class BoletimComponent {
    private $pdo;
    private $escolaId;
    private $tipoPeriodo;
    
    public function __construct($pdo, $escolaId, $tipoPeriodo = 'bimestral') {
        $this->pdo = $pdo;
        $this->escolaId = $escolaId;
        $this->tipoPeriodo = $tipoPeriodo;
    }
    
    /**
     * Renderiza a tabela de boletim
     * @param array $boletim Dados do boletim organizados por disciplina
     * @param array $periodos Períodos a exibir
     * @param array $faltasPorPeriodo Total de faltas por período
     * @return string HTML da tabela
     */
    public function renderizarTabela($boletim, $periodos, $faltasPorPeriodo = []) {
        $html = '<div class="table-responsive">' . PHP_EOL;
        $html .= '<table class="table table-bordered table-sm align-middle text-center small boletim-table">' . PHP_EOL;
        $html .= $this->renderizarCabecalho($periodos);
        $html .= $this->renderizarCorpo($boletim, $periodos, $faltasPorPeriodo);
        $html .= '</table>' . PHP_EOL;
        $html .= '</div>' . PHP_EOL;
        
        return $html;
    }
    
    /**
     * Renderiza o cabeçalho da tabela
     */
    private function renderizarCabecalho($periodos) {
        $labelPeriodo = $this->getLabelPeriodo();
        
        $html = '<thead class="table-light">' . PHP_EOL;
        $html .= '<tr style="font-size: 0.65rem; background: #fdfdfd;">' . PHP_EOL;
        $html .= '<th rowspan="2" class="text-start py-3" style="width: 250px;">Áreas de Conhecimento Disciplinas</th>' . PHP_EOL;
        
        foreach ($periodos as $p) {
            $html .= '<th colspan="3" class="text-uppercase">' . e($p['nome']) . '</th>' . PHP_EOL;
        }
        
        $html .= '<th rowspan="2" style="width: 60px;">AP FINAL</th>' . PHP_EOL;
        $html .= '<th rowspan="2" style="width: 80px;">RECUPERAÇÃO FINAL</th>' . PHP_EOL;
        $html .= '<th rowspan="2" style="width: 60px;">RES FINAL</th>' . PHP_EOL;
        $html .= '</tr>' . PHP_EOL;
        
        $html .= '<tr style="font-size: 0.6rem;">' . PHP_EOL;
        foreach ($periodos as $p) {
            $html .= '<th>NOTA</th>' . PHP_EOL;
            $html .= '<th>FALTA</th>' . PHP_EOL;
            $html .= '<th>F.J.</th>' . PHP_EOL;
        }
        $html .= '</tr>' . PHP_EOL;
        $html .= '</thead>' . PHP_EOL;
        
        return $html;
    }
    
    /**
     * Renderiza o corpo da tabela
     */
    private function renderizarCorpo($boletim, $periodos, $faltasPorPeriodo) {
        $html = '<tbody>' . PHP_EOL;
        
        // Inicializar faltas por período
        $faltasTotal = [];
        foreach ($periodos as $p) {
            $faltasTotal[$p['id']] = $faltasPorPeriodo[$p['id']] ?? 0;
        }
        
        // Renderizar disciplinas
        foreach ($boletim as $disciplina => $dados) {
            $somaNotas = 0;
            $contNotas = 0;
            
            $html .= '<tr>' . PHP_EOL;
            $html .= '<td class="text-start fw-bold text-uppercase" style="font-size: 0.7rem;">' . e($disciplina) . '</td>' . PHP_EOL;
            
            foreach ($periodos as $p) {
                $pid = $p['id'];
                $n = $dados[$pid]['nota'] ?? '--';
                $f = $dados[$pid]['faltas'] ?? 0;
                
                if ($n !== '--' && $n !== '-') {
                    $somaNotas += (float)$n;
                    $contNotas++;
                }
                
                $html .= '<td class="fw-bold">' . e($n) . '</td>' . PHP_EOL;
                $html .= '<td class="' . ($f > 0 ? 'text-danger' : 'text-muted') . '">' . (int)$f . '</td>' . PHP_EOL;
                $html .= '<td class="text-muted">0</td>' . PHP_EOL;
            }
            
            $media = $contNotas > 0 ? round($somaNotas / $contNotas, 1) : '--';
            $html .= '<td class="fw-bold bg-light">' . e($media) . '</td>' . PHP_EOL;
            $html .= '<td class="text-muted">---</td>' . PHP_EOL;
            $html .= '<td class="fw-bold bg-light">' . e($media) . '</td>' . PHP_EOL;
            $html .= '</tr>' . PHP_EOL;
        }
        
        // Linha de total de faltas
        $html .= '<tr class="fw-bold" style="background: #fafafa;">' . PHP_EOL;
        $html .= '<td class="text-start">TOTAL DE FALTAS</td>' . PHP_EOL;
        
        foreach ($periodos as $p) {
            $html .= '<td>--</td>' . PHP_EOL;
            $html .= '<td class="text-danger">' . $faltasTotal[$p['id']] . '</td>' . PHP_EOL;
            $html .= '<td>0</td>' . PHP_EOL;
        }
        
        $html .= '<td>--</td>' . PHP_EOL;
        $html .= '<td>--</td>' . PHP_EOL;
        $html .= '<td>--</td>' . PHP_EOL;
        $html .= '</tr>' . PHP_EOL;
        $html .= '</tbody>' . PHP_EOL;
        
        return $html;
    }
    
    /**
     * Renderiza o cabeçalho informativo do boletim
     */
    public function renderizarCabecalhoInfo($escolaNome, $turmaNome, $serie, $ano = null) {
        $ano = $ano ?? date('Y');
        
        $html = '<div class="small mb-4 text-dark border-bottom pb-2">' . PHP_EOL;
        $html .= '<strong>Escola:</strong> ' . e($escolaNome) . ' | ' . PHP_EOL;
        $html .= '<strong>Turma:</strong> ' . e($turmaNome) . ' | ' . PHP_EOL;
        $html .= '<strong>Ano de Escolaridade:</strong> ' . e($serie) . ' | ' . PHP_EOL;
        $html .= '<strong>Ano Escolar:</strong> ' . e($ano) . PHP_EOL;
        $html .= '</div>' . PHP_EOL;
        
        return $html;
    }
    
    /**
     * Renderiza o CSS padrão do boletim
     */
    public static function renderizarCSS() {
        return <<<CSS
<style>
    .boletim-table {
        margin-bottom: 0;
    }
    
    .boletim-table td, 
    .boletim-table th { 
        border: 1px solid #e0e0e0 !important; 
        padding: 0.4rem 0.2rem;
    }
    
    .boletim-table thead th { 
        vertical-align: middle; 
        font-weight: 600; 
        color: #555;
        background-color: #f8f9fa;
    }
    
    .boletim-table tbody tr:hover {
        background-color: #f9f9f9;
    }
    
    .boletim-table .text-danger {
        font-weight: 500;
    }
    
    .boletim-table .bg-light {
        background-color: #f8f9fa !important;
    }
</style>
CSS;
    }
    
    /**
     * Obtém o rótulo do período baseado no tipo
     */
    private function getLabelPeriodo() {
        $labels = [
            'bimestral' => 'Bimestre',
            'trimestral' => 'Trimestre',
            'semestral' => 'Semestre'
        ];
        
        return $labels[$this->tipoPeriodo] ?? 'Período';
    }
}
