<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings em português do Brasil para mod_oralassessment.
 *
 * @package mod_oralassessment
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['aifailed'] = 'O serviço de IA não estava disponível. A sua transcrição foi salva e continua disponível ' .
    'para revisão do professor.';
$string['aigeneratednotice'] = 'As evidências geradas por IA são auxiliares e devem ser interpretadas por uma pessoa ' .
    'responsável pela revisão.';
$string['allowfollowup'] = 'Permitir perguntas adaptativas de follow-up';
$string['assessmentfinished'] = 'Avaliação enviada';
$string['attempt'] = 'Tentativa';
$string['attemptclosed'] = 'Esta tentativa já está encerrada.';
$string['audio'] = 'Áudio';
$string['audiostored'] = 'Áudio armazenado.';
$string['audiouploadfailed'] = 'Não foi possível armazenar o áudio.';
$string['awaitingreview'] = 'Esta tentativa formativa está aguardando revisão do professor.';
$string['browserprivacy'] = 'O reconhecimento de fala pode ser implementado pelo navegador ou pelo sistema operacional e ' .
    'pode usar o serviço de voz desse fornecedor. O Moodle recebe a transcrição resultante; se ' .
    'preferir, use o campo digitado.';
$string['completionattempt'] = 'O aluno deve enviar a avaliação oral';
$string['completiondetail:attempt'] = 'Enviar uma tentativa de avaliação oral';
$string['completiondetail:reviewed'] = 'Ter a avaliação oral revisada por um professor';
$string['completionreviewed'] = 'Um professor deve revisar a tentativa';
$string['consentrequired'] = 'Confirme o aviso de armazenamento de áudio antes de iniciar a gravação.';
$string['criteria'] = 'Critérios de avaliação';
$string['criteria_help'] = 'Critérios usados para organizar evidências para revisão docente. A IA não aplica a nota final.';
$string['currentquestion'] = 'Pergunta atual';
$string['duration'] = 'Duração máxima';
$string['emptytranscript'] = 'Informe ou capture uma transcrição antes de enviar.';
$string['errorduration'] = 'Use uma duração entre 30 segundos e 2 horas.';
$string['errorgrade'] = 'Use uma nota máxima entre 0 e 1000.';
$string['errorrounds'] = 'Use um valor entre 1 e 20.';
$string['eventattemptreviewed'] = 'Tentativa de avaliação oral revisada';
$string['eventattemptstarted'] = 'Tentativa de avaliação oral iniciada';
$string['eventattemptsubmitted'] = 'Tentativa de avaliação oral enviada';
$string['evidence'] = 'Evidências relacionadas aos critérios';
$string['feedback'] = 'Feedback do professor';
$string['grade'] = 'Nota';
$string['initialquestions'] = 'Perguntas iniciais opcionais';
$string['initialquestions_help'] = 'Informe uma pergunta por linha. Se ficar vazio, a IA cria a primeira pergunta a partir dos ' .
    'objetivos.';
$string['inprogress'] = 'Em andamento';
$string['invalidattempt'] = 'Tentativa de avaliação oral inválida.';
$string['maxgrade'] = 'Nota máxima';
$string['modulename'] = 'Avaliação oral';
$string['modulenameplural'] = 'Avaliações orais';
$string['noattempts'] = 'Ainda não há tentativas.';
$string['notgraded'] = 'Sem nota';
$string['objectives'] = 'Objetivos de aprendizagem';
$string['objectives_help'] = 'Defina os conhecimentos ou habilidades dentro dos quais a conversa oral deve permanecer.';
$string['oralassessment:addinstance'] = 'Adicionar uma avaliação oral';
$string['oralassessment:reviewattempts'] = 'Revisar tentativas da avaliação oral';
$string['oralassessment:view'] = 'Visualizar avaliação oral';
$string['oralassessmentname'] = 'Nome';
$string['pluginadministration'] = 'Administração da avaliação oral';
$string['pluginname'] = 'Avaliação oral';
$string['privacy:metadata:aibridge'] = 'O texto desta atividade é enviado pelo local_ai_bridge usando o purpose ' .
    'oralassessment-dialogue. Este plugin não chama diretamente nenhum provedor externo de IA.';
$string['privacy:metadata:aibridge:conversation'] = 'Perguntas e transcrições das respostas do estudante necessárias ' .
    'para o diálogo.';
$string['privacy:metadata:aibridge:criteria'] = 'Critérios de avaliação definidos pelo professor.';
$string['privacy:metadata:aibridge:objectives'] = 'Objetivos de aprendizagem definidos pelo professor que limitam o diálogo.';
$string['privacy:metadata:aibridge:previousquestion'] = 'Pergunta anterior, usada para gerar um follow-up dentro do escopo.';
$string['privacy:metadata:aibridge:rubric'] = 'Texto da rubrica definido pelo professor, quando configurado.';
$string['privacy:metadata:attempts'] = 'Armazena o estado da tentativa, elementos auxiliares gerados por IA, ' .
    'revisão docente e nota.';
$string['privacy:metadata:attempts:aierror'] = 'Mensagem operacional não sensível caso a geração por IA falhe.';
$string['privacy:metadata:attempts:aievidence'] = 'Evidências organizadas por IA em relação aos critérios.';
$string['privacy:metadata:attempts:aireviewpoints'] = 'Pontos para revisão sugeridos pela IA.';
$string['privacy:metadata:attempts:aisummary'] = 'Resumo gerado por IA para revisão.';
$string['privacy:metadata:attempts:feedback'] = 'Feedback do professor.';
$string['privacy:metadata:attempts:grade'] = 'Nota aplicada por uma pessoa responsável pela revisão.';
$string['privacy:metadata:attempts:reviewedby'] = 'ID do usuário que revisou a tentativa.';
$string['privacy:metadata:attempts:status'] = 'Status da tentativa.';
$string['privacy:metadata:attempts:timemodified'] = 'Quando o registro da tentativa foi alterado pela última vez.';
$string['privacy:metadata:attempts:timereviewed'] = 'Quando o professor revisou a tentativa.';
$string['privacy:metadata:attempts:timestarted'] = 'Quando a tentativa começou.';
$string['privacy:metadata:attempts:timesubmitted'] = 'Quando o estudante enviou a tentativa.';
$string['privacy:metadata:attempts:userid'] = 'ID do aluno.';
$string['privacy:metadata:audio'] = 'Gravações de áudio opcionais das respostas do aluno.';
$string['privacy:metadata:turns'] = 'Armazena as perguntas e transcrições do aluno que formam a conversa da avaliação oral.';
$string['privacy:metadata:turns:aijson'] = 'Saída estruturada da IA associada a uma pergunta gerada.';
$string['privacy:metadata:turns:question'] = 'Pergunta apresentada ao aluno.';
$string['privacy:metadata:turns:role'] = 'Indica se o turno é uma pergunta do assistente ou resposta do estudante.';
$string['privacy:metadata:turns:timecreated'] = 'Quando o turno da conversa foi criado.';
$string['privacy:metadata:turns:transcript'] = 'Transcrição ou resposta digitada enviada pelo aluno.';
$string['privacy:metadata:turns:transcriptionmethod'] = 'Como a transcrição foi produzida.';
$string['privacywarning'] = 'Esta atividade pode processar uma resposta falada. Conforme a configuração, o Moodle ' .
    'armazena apenas a transcrição ou a transcrição junto com o áudio gravado.';
$string['purpose'] = 'Purpose de IA: oralassessment-dialogue';
$string['questionsandresponses'] = 'Perguntas e respostas';
$string['record'] = 'Gravar';
$string['recordingconsent'] = 'Entendo que, se eu gravar áudio, a gravação será armazenada no Moodle junto com esta tentativa.';
$string['recordingnotice'] = 'A gravação de áudio está ativada nesta atividade. A gravação só começa depois que você ' .
    'pressionar o botão de gravar e autorizar o microfone no navegador.';
$string['requirereview'] = 'Exigir revisão do professor';
$string['requirereview_help'] = 'Quando ativado, uma tentativa enviada fica indicada como aguardando revisão humana. A ' .
    'conclusão da atividade no Moodle continua sendo controlada separadamente pelas regras de ' .
    'conclusão.';
$string['reviewattempt'] = 'Revisar tentativa';
$string['reviewattempts'] = 'Revisar tentativas';
$string['reviewed'] = 'Revisada';
$string['reviewedby'] = 'Revisada por';
$string['reviewoptional'] = 'A tentativa formativa foi concluída. A revisão do professor é opcional nesta atividade.';
$string['reviewpoints'] = 'Pontos que merecem revisão do professor';
$string['reviewsperformed'] = 'Revisões realizadas';
$string['rounds'] = 'Quantidade máxima de rodadas';
$string['rubric'] = 'Rubrica';
$string['rubric_help'] = 'Rubrica opcional enviada à IA somente para organizar evidências. Ela não é usada para ' .
    'atribuir nota automaticamente.';
$string['savereview'] = 'Salvar revisão humana e aplicar nota';
$string['startassessment'] = 'Iniciar avaliação';
$string['started'] = 'Iniciada';
$string['status'] = 'Status';
$string['stoprecording'] = 'Parar gravação';
$string['storeaudio'] = 'Guardar gravações de áudio';
$string['storeaudio_help'] = 'Quando ativado, o áudio capturado no navegador pode ser armazenado no Moodle. Nesta versão o ' .
    'AI Bridge recebe somente texto.';
$string['student'] = 'Aluno';
$string['submitresponse'] = 'Enviar resposta';
$string['submitted'] = 'Enviada';
$string['summary'] = 'Resumo da IA para revisão';
$string['timeexpired'] = 'O tempo configurado para a avaliação terminou.';
$string['timeleft'] = 'Tempo restante';
$string['transcript'] = 'Transcrição / resposta digitada';
$string['transcriptionbrowser'] = 'Reconhecimento de voz do navegador quando disponível, com fallback digitado';
$string['transcriptionmanual'] = 'Somente transcrição digitada';
$string['transcriptionmode'] = 'Modo de transcrição';
$string['transcriptonlynotice'] = 'O áudio não é armazenado. O reconhecimento de voz do navegador pode ser usado quando ' .
    'disponível, e você sempre pode digitar ou editar a transcrição antes de enviar.';
$string['transcripttoolong'] = 'A transcrição é longa demais.';
