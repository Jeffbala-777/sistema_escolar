<?php
// app/ia/prompts.php

function getPromptResumoAluno($historicoTexto) {
    return "
Você é um coordenador pedagógico experiente, com mais de 15 anos de carreira.

Faça um resumo claro, objetivo, profissional e bem estruturado do histórico do aluno.

**Regras obrigatórias:**
- Escreva em português brasileiro, linguagem formal mas acessível.
- Organize o resumo exatamente nestas seções:
- **Desempenho Acadêmico**
- **Comportamento e Participação**
- **Pontos Fortes**
- **Dificuldades e Pontos de Atenção**
- **Evolução Geral**
- **Recomendações**

Histórico completo do aluno:

{$historicoTexto}
";
}

// Prompt alternativo (mais curto)
function getPromptResumoCurto($historicoTexto) {
    return "
Resuma de forma objetiva o histórico escolar do aluno abaixo. 
Destaque desempenho acadêmico, comportamento, pontos fortes, dificuldades e recomendações.

Histórico:
{$historicoTexto}
";
}