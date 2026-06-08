<?php
// app/ia/prompts.php

function getPromptResumoAluno($historicoTexto) {
    return "
Analise exclusivamente os dados reais fornecidos pelo sistema para este aluno.

Considere:
- Notas
- Frequência/Faltas
- Desempenho por disciplina
- Evolução entre períodos
- Observações e relatórios cadastrados pelos professores
- Qualquer outro dado disponível no sistema

IMPORTANTE:
- Utilize APENAS os dados recebidos.
- Não invente informações.
- Não crie dados fictícios.
- Não faça suposições.
- Não solicite dados adicionais.
- Não diga que faltam informações quando o período selecionado for \"Todos os Períodos\".
- Quando \"Todos os Períodos\" estiver selecionado, utilize todos os dados históricos disponíveis no sistema para gerar a análise.
- Mesmo que alguns períodos possuam menos informações que outros, faça a análise com os dados existentes.
- Nunca responda que não é possível analisar por falta de dados quando houver informações cadastradas no sistema.

A análise deve conter:
1. Resumo Geral
2. Desempenho Acadêmico
3. Frequência e Faltas
4. Evolução ao longo dos períodos
5. Pontos Fortes
6. Pontos de Atenção
7. Recomendações Pedagógicas

A linguagem deve ser clara, objetiva, profissional e adequada ao ambiente escolar.

Não mencionar limitações da IA.
Não mencionar ausência de dados quando houver informações disponíveis.
Basear toda a análise nos dados recebidos.

Histórico completo do aluno:
{$historicoTexto}
";
}

// Prompt alternativo (mais curto)
function getPromptResumoCurto($historicoTexto) {
    return "
Resuma de forma objetiva o histórico escolar do aluno abaixo, baseando-se exclusivamente nos dados reais fornecidos.
Destaque desempenho acadêmico, comportamento, pontos fortes, dificuldades e recomendações.

Histórico:
{$historicoTexto}
";
}
