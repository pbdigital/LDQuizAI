<div class="wrap" id="ldqm-app">
    <h2><?= __('LearnDash Quiz Maker', 'learndash-quiz-maker') ?></h2>

    <div class="tools-container">
        <div class="col-1">
            <form action="" method="post">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="quiz_title"><?= __('Chat GPT Token', 'learndash-quiz-maker') ?></label>
                            </th>
                            <td>
                                <input type="text" name="quiz_title" id="quiz_title" class="regular-text" v-model="token">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="quiz_title"><?= __('Quiz Title', 'learndash-quiz-maker') ?></label>
                            </th>
                            <td>
                                <input type="text" name="quiz_title" id="quiz_title" class="regular-text" v-model="title">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="quiz_topic"><?= __('Quiz Topic', 'learndash-quiz-maker') ?></label>
                            </th>
                            <td>
                                <input type="text" name="quiz_topic" id="quiz_topic" class="regular-text" v-model="topic">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="quiz_complexity"><?= __('Quiz Complexity', 'learndash-quiz-maker') ?></label>
                            </th>
                            <td>
                                <input type="text" name="quiz_complexity" id="quiz_complexity" class="regular-text" v-model="complexity">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="quiz_tone"><?= __('Quiz Tone', 'learndash-quiz-maker') ?></label>
                            </th>
                            <td>
                                <input type="text" name="quiz_tone" id="quiz_tone" class="regular-text" v-model="tone">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="instructions"><?= __('Additional Instructions For The API', 'learndash-quiz-maker') ?></label>
                            </th>
                            <td>
                                <textarea name="instructions" id="instructions" class="regular-text" rows="5" v-model="instructions"></textarea>
                            </td>
                        </tr>
                    </tbody>

                </table>
                <div id="response-container" style="background: #eee; display: none;">
                    This would be hidden
                </div>
                <p class="submit">
                    <button v-if="generating" id="generate-btn" class="button button-primary" disabled>
                        <?= __('Generating Questions...', 'learndash-quiz-maker') ?>
                    </button>
                    <button v-else type="button" id="generate-btn" class="button button-primary" @click.prevent="generateQuestions">
                        <template v-if="generated">
                            Generate 3 more...
                        </template>
                        <template v-else>
                            Generate Questions
                        </template>
                    </button>
                </p>
            </form>
        </div>
        <div class="col-2">
            <h3 v-if="questions.length"><?= __('Questions', 'learndash-quiz-maker') ?></h3>
            <div id="questions-container">
                <template v-for="(question, index) in questions">
                    <div class="question-container">
                        <div class="question">
                            <!-- button group move up and down -->
                            <div class="btn-group-move">
                                <button type="button" class="btn-arrow" @click.prevent="moveQuestionUp(index)" v-if="index != 0">
                                    <span class="dashicons dashicons-arrow-up-alt2"></span>
                                </button>
                                <button type="button" class="btn-arrow" @click.prevent="moveQuestionDown(index)" v-if="index != questions.length - 1">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </button>
                            </div>
                            <label for="question">Q:{{index+1}} </label>
                            <input type="text" name="question" id="question" v-model="questions[index].question">
                            <button type="button" class="button button-primary delete" @click.prevent="deleteQuestion(index)">Delete</button>
                            <button class="collapse-btn" @click.prevent="toggleQuestionCollapse(index)">
                                <span class="dashicons dashicons-arrow-up-alt2" v-if="question.collapse_open"></span>
                                <span class="dashicons dashicons-arrow-down-alt2" v-else></span>
                            </button>
                        </div>
                        <div class="answers-container" :class="!question.collapse_open ? 'hidden-by-collapse' : ''">
                            <template v-for="(choice, c_index) in questions[index].choices">
                                <div class="answer-container">
                                    <div class="btn-group-move">
                                        <button type="button" class="btn-arrow" @click.prevent="moveAnswerUp(index, c_index)" v-if="c_index != 0">
                                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                                        </button>
                                        <button type="button" class="btn-arrow" @click.prevent="moveAnswerDown(index, c_index)" v-if="c_index != questions[index].choices.length - 1">
                                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                                        </button>
                                    </div>
                                    <label :for="'answer-' + choice.id">{{ answer_keys[c_index] }}</label>
                                    <input type="text" :name="'answer-' + choice.id" :id="'answer-' + choice.id" v-model="questions[index].choices[c_index].text">
                                    <div class="ct-answer-correct">
                                        <label>
                                            <input type="radio" :name="'correct-answer-' + index" :id="'correct-answer-' + choice.id" :value="choice.id" :checked="questions[index].choices[c_index].isCorrect" @change="toggleCorrectAnswer(index, c_index)" class="radio-bt-answer"> Correct
                                        </label>
                                    </div>
                                    <button type="button" class="button button-primary delete" @click.prevent="deleteChoice(index, c_index)">Delete</button>
                                </div>
                            </template>

                            <div class="add-answer-container" v-if="questions[index].choices.length <= 4">
                                <button type="button" class="button button-primary" @click.prevent="addChoice(index)">Add Answer</button>
                            </div>
                        </div>
                       
                        <!-- <div class="correct-answer-container">
                            <label for="correct-answer">Correct Answer: </label>
                            <input type="text" name="correct-answer" id="correct-answer" v-model="questions[index].correctAnswer">
                        </div> -->
                    </div>
                </template>
                
                
                <template v-if="questions.length">
                    <a :href="quiz_link" class="button button-primary" v-if="quiz_created">View Quiz</a>
                    <button type="button" class="button button-primary" v-else v-on:click="saveQuiz" :disabled="submitting_quiz">Save Quiz</button>
                </template>
            </div>
        </div>
    </div>
</div>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    const {
        createApp
    } = Vue

    createApp({
        data() {
            return {
                // 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
                // message: 'Hello Vue!',
                messages: [],
                token: '<?= get_option('chatgpt_token') ?>',
                title: '',
                topic: '',
                complexity: '',
                tone: '',
                instructions: '',
                answer_keys: ['A', 'B', 'C', 'D', 'E'],
                questions: [],
                generating: false,
                generated: false,
                quiz_settings: {
                    course: 0,
                    lesson: 0,
                },
                trigger_started: false,
                submitting_quiz: false,
                quiz_created: false,
                quiz_link: null
            }
        },
        mounted() {
            this.messages = [{
                    role: "system",
                    content: 'As an AI assistant designed to create Multiple Choice quizzes for learndash, you will receive a title, topic, tone, and complexity as input. Your task is to generate 3 multiple choice questions with answer options and a correct answer in a minified JSON format. Please ensure that your output contains no extraneous text, descriptions, or warnings.\n{"quiz":{"questions":[{"question":"What is the capital of France?","choices":[{"id":"A","text":"Paris"},{"id":"B","text":"London"},{"id":"C","text":"Berlin"},{"id":"D","text":"Germany"}],"correctAnswer":"A"}...]}}',
                }
            ];
        },
        watch: {
            questions: function(newquestions, oldquestions) {
                console.log('newquestions', newquestions)
            }
        },
        methods: {
            async generateQuestions(event) {
                try {
                    console.log('Generating questions...')

                    jQuery.post('<?= admin_url( 'admin-ajax.php' ) ?>', {
                        action: 'set_chatgpt_token',
                        token: this.token,
                    }, function(response) {
                        console.log('response', response)
                    })

                    this.generating = true

                    var to_push_messages = this.messages
                    
                    let instructions = "n/a";
                    if (this.instructions.length > 0)
                    {
                        instructions = this.instructions;
                    }
                    if (!this.trigger_started) {
                        to_push_messages.push({
                            role: "user",
                            content: "Title: " +
                                this.title +
                                "\nTopic: " +
                                this.topic +
                                "\nComplexity: " +
                                this.complexity +
                                "\nTone: " +
                                this.tone +
                                "\nAdditional Instructions: " +
                                instructions,
                        })
                    } else {
                        let content = "3 more questions.";
                        if (this.instructions.length > 0)
                        {
                            content = "3 more questions. Additional Instructions: " +
                            this.instructions;
                        }
                        
                        to_push_messages.push({
                            role: "user",
                            content: content
                        })
                    }
                    this.trigger_started = true
                    
                    

                    const response = await fetch('https://api.openai.com/v1/chat/completions', {
                        method: "POST", // *GET, POST, PUT, DELETE, etc.
                        // mode: "cors", // no-cors, *cors, same-origin
                        // cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
                        // credentials: "same-origin", // include, *same-origin, omit
                        headers: {
                            Authorization: "Bearer " + this.token,
                            "Content-Type": "application/json",
                            // 'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        // redirect: "follow", // manual, *follow, error
                        // referrerPolicy: "no-referrer", 
                        body: JSON.stringify({
                            messages: to_push_messages,
                            temperature: 0.7,
                            max_tokens: 993,
                            top_p: 1,
                            frequency_penalty: 0,
                            presence_penalty: 0,
                            model: "gpt-3.5-turbo",
                            stream: false,
                        })
                    })

                    let promiseData = response.json()

                    promiseData.then((data) => {
                        var responseContent = JSON.stringify(data.choices[0].message.content);
                        var jsonString = null;
                    
                        // Attempt to parse the entire string as JSON first
                        try {
                            jsonString = JSON.parse(responseContent);
                        } catch (error) {
                            console.log('The entire response is not a JSON object');
                        }
                    
                        // If the above failed, the JSON object is probably contained within the string
                        if (!jsonString) {
                            var jsonStartIndex = responseContent.indexOf('{');
                    
                            if (jsonStartIndex !== -1) {
                                // Cut the string from where the JSON starts
                                jsonString = responseContent.substring(jsonStartIndex);
                            }
                        }
                    
                        // If we found a JSON object, process it
                        if (jsonString) {
                            try {
                                let questionsJson = JSON.parse(jsonString);
                    
                                this.messages.push({
                                    role: "assistant",
                                    content: jsonString
                                });
                    
                                let questions = questionsJson?.quiz?.questions;
                                questions.forEach((question, index) => {
                                    question.choices.forEach((choice, c_index) => {
                                        choice.isCorrect = (choice.id == question.correctAnswer) ? true : false;
                                    });
                                    question.collapse_open = false;
                                    this.questions.push(question);
                                });
                                this.generating = false;
                                this.generated = true;
                            } catch (error) {
                                console.log('api error', error);
                                alert('Something went wrong with the API. Please try again.');
                                this.generating = false;
                            }
                        } else {
                            alert(responseContent);
                        }
                    });

                } catch (error) {
                    this.generating = false
                    alert('Something went wrong with the API. Please try again.')
                }
            },
            extractJSON(content) {
                var regex = /{.*}/; // regex pattern to extract JSON object

                // match regex pattern in the response content
                var match = content.match(regex);

                if (match === null) {
                    // no valid JSON found in the response
                    return null;
                } else {
                    // valid JSON found, return the matched string
                    return match[0];
                }
            },
            deleteQuestion(index) {
                this.questions.splice(index, 1)
            },
            deleteChoice(question_index, choice_index) {
                this.questions[question_index].choices.splice(choice_index, 1)
            },
            addChoice(question_index) {
                this.questions[question_index].choices.push({
                    // id: (this.questions[question_index].choices.length + 1),
                    id: this.answer_keys[this.questions[question_index].choices.length],
                    text: ''
                })
            },
            toggleCorrectAnswer(question_index, answer_index) {
                this.questions[question_index].choices.forEach((choice, index) => {
                    choice.isCorrect = (index == answer_index) ? true : false
                })
            },
            toggleQuestionCollapse(question_index) {
                this.questions[question_index].collapse_open = !this.questions[question_index].collapse_open
            },
            moveQuestionUp(question_index) {
                if (question_index > 0) {
                    let temp = this.questions[question_index - 1]
                    this.questions[question_index - 1] = this.questions[question_index]
                    this.questions[question_index] = temp
                }
            },
            moveQuestionDown(question_index) {
                if (question_index < this.questions.length - 1) {
                    let temp = this.questions[question_index + 1]
                    this.questions[question_index + 1] = this.questions[question_index]
                    this.questions[question_index] = temp
                }
            },
            moveAnswerUp(question_index, answer_index) {
                if (answer_index > 0) {
                    let temp = this.questions[question_index].choices[answer_index - 1]
                    this.questions[question_index].choices[answer_index - 1] = this.questions[question_index].choices[answer_index]
                    this.questions[question_index].choices[answer_index] = temp
                }
            },
            moveAnswerDown(question_index, answer_index) {
                if (answer_index < this.questions[question_index].choices.length - 1) {
                    let temp = this.questions[question_index].choices[answer_index + 1]
                    this.questions[question_index].choices[answer_index + 1] = this.questions[question_index].choices[answer_index]
                    this.questions[question_index].choices[answer_index] = temp
                }
            },
            async saveQuiz() {
                console.log('this.questions now', this.questions)

                // AJAx call to save quiz
                // fetch post to ldm_create_quiz
                // on success, redirect to quiz page
                // on error, display error message
                let self = this
                this.submitting_quiz = true
                jQuery.post('<?= admin_url( 'admin-ajax.php' ) ?>', {
                    action: 'ldm_create_quiz',
                    quiz_title: this.title,
                    topic: this.topic,
                    complexity: this.complexity,
                    tone: this.tone,
                    questions: this.questions,
                    quiz_settings: this.quiz_settings
                }, function(response) {
                    console.log('response', response)
                    let data = JSON.parse(response)
                    // let data = response
                    self.quiz_created = true
                    if (data.success) {
                        // self.quiz_link = data.quiz_link
                        // clean link data.quiz_link
                        // console.log('right', data.quiz_link.replace(/\\/g, ''))
                        let link = data.quiz_link.replace('&amp;', '&')
                        self.quiz_link = link
                    }
                    
                    // if (response.success) {
                    //     window.location.href = response.data
                    // } else {
                    //     alert(response.data)
                    // }
                })
            }
        }
    }).mount('#ldqm-app')
</script>
<style>
    .answers-container.hidden-by-collapse {
        display: none;
    }

    button.collapse-btn {
        margin-left: 7px;
        border: 0;
        cursor: pointer;
    }
    
    .tools-container {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }

    .col-1 {
        width: 50%;
    }

    .col-2 {
        width: 50%;
    }

    .question-container {
        display: flex;
        flex-direction: column;
        margin-bottom: 20px;
        border-bottom: 1px solid #ccc;
        padding-bottom: 20px;
    }


    .correct-answer-container {
        display: flex;
        flex-direction: row;
        align-items: center;
        margin-top: 12px;
    }

    .answer-container {
        display: flex;
        flex-direction: row;
        align-items: center;
        margin-top: 12px;
    }

    .question-container .question {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .answer-container label,
    .correct-answer-container label,
    .question-container .question label {
        margin-right: 10px;
    }

    button.delete {
        margin-left: 10px !important;
        background-color: red !important;
        border-color: red !important;
        ;
    }

    
    .question-container .question input {
        width: 86%;
    }

    .answer-container input {
        width: 60%;
    }

    .add-answer-container {
        margin-top: 12px;
    }

    .radio-bt-answer {
        width: unset !important;
        margin-left: 4px !important;
    }

    .btn-group-move {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        margin-right: 10px;
    }

    .answers-container {
        padding-left: 40px;
    }
</style>