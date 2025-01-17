<template>
    <div>
        <div>
            <div class="inline-flex space-x-1">
                <badge :name="cloneCardData.badge.name" v-if="cloneCardData.badge !== []"></badge>

                <div
                    class="px-3 py-1 rounded text-xs font-semibold flex items-center"
                    :class="`bg-${statusColor}-200 text-${statusColor}-800`">
                <span class="w-2 h-2 rounded-full mr-1"
                      :class="`bg-${statusColor}-800`"
                ></span>
                    <span> status: {{ cloneCardData.status }}</span>
                </div>

                <div :style="`background-color: hsl( ${priorityHue}, 45%, 90%); color: hsl( ${priorityHue}, 50%, 45%)`"
                     class="px-3 py-1 rounded text-xs font-semibold flex items-center">
                <span class="w-2 h-2 rounded-full mr-1"
                      :style="`background-color: hsl( ${priorityHue}, 45%, 40%)`"></span>
                    <span>{{ timeRemaining }}</span>
                </div>
            </div>
            <div class="flex flex-wrap">

                <div class="py-2 flex-1 pr-3">
                    <p class="text-gray-700 font-semibold font-sans tracking-wide text-2xl py-1">
                        {{ cloneCardData.name }}
                    </p>
                    <div v-if="cloneCardData.row_id !== null"
                         class="flex">
                        <p
                            class="text-gray-500 text-sm py-1"> Row:
                            <i class="font-semibold">'{{ cloneCardData.row.name }}'</i> Column:
                            <i class="font-semibold">'{{ cloneCardData.column.name }}'</i>
                        </p>
                        <button @click="removeFromRowAndColumn()"
                                class="px-2 text-xs text-indigo-600 hover:text-indigo-800 transition duration-300 ease-in-out focus:outline-none">
                            remove
                        </button>
                    </div>

                    <p v-else class="text-gray-500 text-sm py-1"> In Backlog</p>
                </div>


                <div v-if="$role === 'admin'"
                     class="py-4 w-60">
                <span class="block text-xs font-bold leading-4 tracking-wide uppercase text-gray-600">
                    Status
                </span>
                    <vSelect
                        v-model="selectedStatus"
                        :options="this.statuses"
                        label="name"
                        style="margin-top: 7px"
                        class="text-gray-700"
                        @input="setStatus()">
                        <template slot="option" slot-scope="option">
                            <i class="fas fa-circle mr-2" :class="`text-${option.color}-400 `"></i>
                            {{ option.name }}
                        </template>
                    </vSelect>
                </div>
            </div>
        </div>

        <div class="flex pt-3 border-t"
             :class="isVerticalMode ? 'flex-col-reverse' : 'space-x-16'">

            <div class="flex-auto">

                <!-- Description-->
                <div class="space-y-5">
                    <p class="text-gray-700 font-semibold font-sans tracking-wide text-lg"> Description: </p>
                    <div class="rounded-lg p-5 bg-gray-50">
                        <div class="ql-editor h-auto" id="task-description" v-html="formatted"></div>
                    </div>
                </div>

                <!-- Files-->
                <div class="space-y-5 pt-5">
                    <p class="text-gray-700 font-semibold font-sans tracking-wide text-lg"> Task Files: </p>
                    <file-pond
                        name="filepond"
                        ref="pond"
                        :files="formattedFiles"
                        :label-idle="`${formattedFiles.length} attached files`"
                        allow-multiple="true"
                        credits=false
                        maxFileSize="5MB"
                        maxTotalFileSize="10MB"
                        imagePreviewHeight="100"
                        allowRemove="false"
                        allowDrop="false"
                        allowBrowse="false"
                        allowPaste="false"
                        allowReplace="false"
                        allowRevert="false"
                    />
                </div>

                <!-- Comments-->
                <div v-if="isCommentsVisible"
                     class="space-y-5 pt-5">
                    <p class="text-gray-700 font-semibold font-sans tracking-wide text-lg"> Comments: </p>
                    <task-comments :cardData="cloneCardData"></task-comments>
                </div>
            </div>

            <div class="space-y-5 flex-none" :style=" sourceFrom === kanbanTaskModalName? 'max-width: 30%;' : '' ">

                <!-- Info Section-->
                <div>
                    <p class="text-gray-700 font-semibold font-sans tracking-wide text-lg"> Info: </p>
                    <div class="relative mb-5 border-b" v-if="checklistData.total > 0">
                        <div class="flex mb-3 items-center justify-between">
                            <div>
                            <span
                                class="text-xs font-semibold inline-block py-1 px-2 rounded-full text-indigo-600 bg-indigo-200">
                                checklist items: {{ checklistData.done }}/{{ checklistData.total }}
                            </span>
                            </div>
                            <div class="text-right">
                            <span class="text-xs font-semibold inline-block text-indigo-600">{{
                                    checklistPercentage
                                }}%</span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-indigo-200 ">
                            <div :style="`width:${checklistPercentage}%`"
                                 class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-indigo-500"></div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-32 flex text-gray-700">
                                <i class="fas fa-bullhorn mr-2"></i>
                                <p class="font-semibold font-sans tracking-wide text-sm"> Reporter: </p>
                            </div>
                            <avatar :name="cloneCardData.reporter.user.full_name" :size="6" :tooltip="true"
                                    class="border border-white max-w-md"></avatar>
                            <span class="text-xs text-gray-600 px-1"> {{ cloneCardData.reporter.user.full_name }}</span>
                        </div>
                        <div class="flex">
                            <div class="w-32 flex text-gray-700">
                                <i class="fas fa-people-arrows mr-2"></i>
                                <p class="font-semibold font-sans tracking-wide text-sm"> Assigned To: </p>
                            </div>
                            <div class="space-y-2">
                            <span v-if=" cloneCardData.assigned_to.length === 0"
                                  class="text-xs text-red-800 px-2 py-1 bg-red-200 rounded"> unnasigned</span>
                                <template v-for="(employee, employeeIndex) in cloneCardData.assigned_to">
                                    <div class="flex items-center">
                                        <avatar class="border border-white max-w-md"
                                                :key="employeeIndex"
                                                :name="employee.employee.user.full_name"
                                                :size="6"
                                                :tooltip="false"></avatar>
                                        <span
                                            class="text-xs text-gray-600 px-1"> {{
                                                employee.employee.user.full_name
                                            }}</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex my-4 border-t border-b items-center">

                        <div class="py-4 space-y-4">

                            <div v-if="cloneCardData.shared_task_data.erp_employees.length"
                                 class="text-sm flex items-start">
                                <div class="w-32 flex items-center text-gray-700">
                                    <i class="fas fa-user mr-2"></i>
                                    <p class="font-semibold font-sans tracking-wide text-sm">
                                    <span
                                        v-if="cloneCardData.shared_task_data.erp_employees.length > 1">Employees:</span>
                                        <span v-else>Employee:</span>
                                    </p>
                                </div>
                                <div>
                                    <div class="flex" v-for="employee in cloneCardData.shared_task_data.erp_employees">
                                        <avatar :name="employee.full_name" :size="6" :tooltip="true"
                                                class="border border-white max-w-md"></avatar>
                                        <a :href="`/app/users/${employee.id}/edit`" target="_blank"
                                           class="text-xs px-1 text-indigo-500 underline"> {{ employee.full_name }}</a>
                                    </div>
                                </div>
                            </div>

                            <div v-if="cloneCardData.shared_task_data.erp_contracts.length"
                                 class="text-sm text-gray-700 flex items-start">
                                <div class="w-32 flex items-center text-gray-700">
                                    <i class="fas fa-building mr-2"></i>
                                    <p class="font-semibold font-sans tracking-wide text-sm">
                                    <span
                                        v-if="cloneCardData.shared_task_data.erp_contracts.length > 1">Contracts:</span>
                                        <span v-else>Contracts:</span>
                                    </p>
                                </div>
                                <div>
                                    <div class="flex" v-for="contract in cloneCardData.shared_task_data.erp_contracts">
                                        <avatar :name="contract.contract_identifier" :size="6" :tooltip="true"
                                                class="border border-white max-w-md"></avatar>
                                        <a :href="`/app/contracts/${contract.id}/edit`" target="_blank"
                                           class="text-xs  px-1 text-indigo-500 underline">{{
                                                contract.contract_identifier
                                            }}</a>
                                    </div>
                                </div>
                            </div>

                            <div v-if="cloneCardData.deadline !== null" class="text-sm text-gray-700 flex">
                                <div class="w-32 flex items-center">
                                    <i class="fas fa-stopwatch mr-2"></i>
                                    <p class="font-semibold font-sans tracking-wide text-sm">Deadline: </p>
                                </div>
                                <a class="max-w-md text-xs">{{ cloneCardData.deadline | moment("DD MMM, YYYY") }}</a>
                            </div>

                            <div class="text-sm text-gray-700 flex">
                                <div class="w-32 flex items-center">
                                    <i class="fas fa-clock mr-2"></i>
                                    <p class="font-semibold font-sans tracking-wide text-sm">Estimate: </p>
                                </div>
                                <a class="max-w-md text-sm"> {{ cloneCardData.time_estimate }} hours </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Task Section-->

                <div v-if="isRelatedTasksVisible"
                     class="space-y-5 pt-5">
                    <p class="text-gray-700 font-semibold font-sans tracking-wide text-lg"> Related Tasks: </p>
                    <related-tasks :sourceFrom="source" :cardData="cloneCardData"></related-tasks>
                </div>

            </div>
        </div>
    </div>
</template>
<script>
import Avatar from "../Avatar.vue";
import Badge from "../Badge.vue";
import {helperFunctions} from "../../../mixins/helperFunctionsMixin";
import {ajaxCalls} from "../../../mixins/ajaxCallsMixin";
import TaskComments from "./TaskComments";
import TaskCard from "../../kanban/kanbanComponents/TaskCard";
import vSelect from "vue-select";
import RelatedTasks from "./RelatedTasks";
import {componentNames} from "../../../enums/componentNames";

export default {
    inject: ["eventHub"],

    mixins: [helperFunctions, ajaxCalls],

    components: {
        RelatedTasks,
        TaskCard,
        TaskComments,
        Badge,
        Avatar,
        vSelect,
    },

    watch: {
        cardData: function (newVal) {
            this.cloneCardData = newVal;
        }
    },

    props: {
        cardData: Object,
        isVerticalMode: {
            type: Boolean,
            default: false
        },
        isCommentsVisible: {
            type: Boolean,
            default: true
        },
        isRelatedTasksVisible: {
            type: Boolean,
            default: true
        },
        sourceFrom: String,
    },

    data() {
        return {
            source: componentNames.ViewTaskData,
            kanbanTaskModalName: componentNames.KanbanTaskModal,
            loadingSharedData: false,
            tasks: [],
            formatted: null,
            checklistData: {
                total: 0,
                done: 0,
            },
            cloneCardData: null,
            selectedStatus: null,
            statuses: [
                {
                    name: 'active',
                    color: 'green',
                },
                {
                    name: 'completed',
                    color: 'blue',
                },
                {
                    name: 'cancelled',
                    color: 'red',
                }
            ],
            clickedCheckboxEl: null,
            formattedFiles: []
        };
    },

    created() {
        this.eventHub.$on("fetch-and-replace-task-data", () => {
            this.fetchAndReplaceTaskData();
        });
        this.cloneCardData = {...this.cardData};
        this.handleChecklist();
        this.formatFilesForFilepond();
        this.selectedStatus = this.cloneCardData.status;
    },

    methods: {

        formatFilesForFilepond() {
            this.formattedFiles = []
            this.cloneCardData.task_files.forEach((file, index) => {

                this.formattedFiles.push({
                    source: file.full_url,
                    options: {
                        metadata: {
                            path: file.task_file_url,
                            id: file.id
                        },
                    }
                })
            })
        },

        fetchAndReplaceTaskData() {
            this.loadingSharedData = true;
            this.asyncGetTaskData(this.cloneCardData.id).then((data) => {
                this.cloneCardData = data.data;
                this.loadingSharedData = false;
                this.selectedStatus = this.cloneCardData.status;
                this.handleChecklist();
                this.formatFilesForFilepond();
                this.eventHub.$emit("update-add-task-data-with-group-data", data.data);
            })
        },

        removeFromRowAndColumn() {
            this.$swal({
                icon: 'info',
                title: 'Remove task from Row and Column',
                text: 'The task will no longer be visible in the board but will continue to exist in the backlog',
                showCancelButton: true,
                confirmButtonText: `Continue`,
            }).then((result) => {
                if (result.isConfirmed) {
                    let taskPlacementData = {
                        taskId: this.cloneCardData.id,
                        boardId: this.cloneCardData.board_id,
                        rowId: null,
                        columnId: null
                    }
                    this.asyncPlaceTask(taskPlacementData).then(() => {
                        switch (this.sourceFrom) {
                            case componentNames.TaskPane :
                                this.eventHub.$emit("fetch-and-replace-task-pane-data");
                                break;
                            case componentNames.KanbanTaskModal :
                                this.eventHub.$emit("fetch-and-set-column-tasks", this.cloneCardData);
                                this.eventHub.$emit("close-task-modal");
                                break;
                        }
                    })
                }
            })
        },

        setStatus() {
            let swalText = (this.selectedStatus.name === "active"
                ? 'This task will be able to appear inside a board'
                : 'This will archive the task. You can find it in the backlog.');

            if (this.selectedStatus.name !== this.cloneCardData.status) {
                this.$swal({
                    icon: 'info',
                    title: 'Set status to ' + this.selectedStatus.name,
                    text: swalText,
                    showCancelButton: true,
                    confirmButtonText: `Continue`,
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.asyncSetStatus(this.cloneCardData.id, this.selectedStatus.name).then(() => {
                            switch (this.sourceFrom) {
                                case componentNames.TaskPane :
                                    this.eventHub.$emit("fetch-and-replace-task-pane-data");
                                    break;
                                case componentNames.KanbanTaskModal :
                                    this.eventHub.$emit("fetch-and-set-column-tasks", this.cloneCardData);
                                    this.eventHub.$emit("close-task-modal");
                                    break;
                            }
                        })
                    } else {
                        this.selectedStatus = this.cloneCardData.status;
                    }
                })
            } else {
                this.triggerInfoToast('You current status is already set to \'' + this.selectedStatus.name + '\'');
            }
        },

        handleChecklist() {

            let parser = new DOMParser();
            let doc = parser.parseFromString(this.cloneCardData.shared_task_data.description, 'text/html');
            let uls = doc.querySelectorAll('ul[data-checked]');

            if (uls.length === 0) {
                this.formatted = this.cloneCardData.shared_task_data.description;
            }

            for (let i = 0; i < uls.length; i++) {

                let div = document.createElement('div');

                for (let x = 0; x < uls[i].childNodes.length; x++) {
                    let ul = document.createElement('ul');
                    let li = document.createElement('li');

                    ul.setAttribute('data-checked', uls[i].attributes[0].value);
                    li.innerHTML = (uls[i].childNodes[x].innerText);
                    ul.appendChild(li);
                    div.appendChild(ul);
                }

                uls[i].replaceWith(div);
                this.formatted = new XMLSerializer().serializeToString(doc);

                this.$nextTick(() => {
                    let ul = document.querySelectorAll('ul[data-checked]');
                    for (let i = 0; i < ul.length; i++) {
                        ul[i].replaceWith(ul[i].cloneNode(true));
                    }
                });

                this.$nextTick(() => {

                    let ul = document.querySelectorAll('#task-description ul[data-checked]');
                    this.checklistData.total = document.querySelectorAll('#task-description ul[data-checked] li');

                    let done = document.querySelectorAll('#task-description ul[data-checked="true"] li');
                    let total = document.querySelectorAll('#task-description ul[data-checked] li');

                    this.checklistData.done = done.length;
                    this.checklistData.total = total.length;


                    for (let i = 0; i < ul.length; i++) {
                        ul[i].addEventListener("click", el => {

                            this.clickedCheckboxEl = el.currentTarget;

                            let toggle = this.clickedCheckboxEl.getAttribute("data-checked") === 'true' ? "false" : "true";
                            this.clickedCheckboxEl.setAttribute('data-checked', toggle);
                            this.cloneCardData.shared_task_data.description = (document.getElementById('task-description').innerHTML);

                            let done = document.querySelectorAll('#task-description ul[data-checked="true"] li');
                            let total = document.querySelectorAll('#task-description ul[data-checked] li');

                            this.checklistData.done = done.length;
                            this.checklistData.total = total.length;

                            this.asyncUpdateDescription({
                                'description': this.cloneCardData.shared_task_data.description,
                                'id': this.cloneCardData.id,
                                'isChecked': this.clickedCheckboxEl.getAttribute("data-checked"),
                                'checkboxContent': this.clickedCheckboxEl.querySelector('li').innerHTML
                            }).then(() => {
                                this.asyncGetTaskData(this.cloneCardData.id).then((data) => {
                                    this.cloneCardData = data.data;
                                    this.eventHub.$emit("update-add-task-data-with-group-data", data.data);
                                })
                            })

                        }, true);
                    }
                })
            }
        },
    },

    computed: {
        priorityHue() {
            if (this.cloneCardData.hours_to_deadline === null) {
                return 0;
            } else if (this.cloneCardData.hours_to_deadline <= 0) {
                return 0;
            } else if (this.cloneCardData.hours_to_deadline >= 200) {
                return 100;
            } else {
                return this.cloneCardData.hours_to_deadline / 2;
            }
        },

        timeRemaining() {
            if (this.cloneCardData.hours_to_deadline <= 0) {
                return ('Time Remaining: 0 days, 0 hours');
            }
            if (this.cloneCardData.hours_to_deadline) {
                let totalHours = this.cloneCardData.hours_to_deadline;
                let days = Math.floor(this.cloneCardData.hours_to_deadline / 24);
                let hours = totalHours - days * 24
                return ('Time Remaining: days: ' + days + ', hours: ' + hours);
            } else {
                return ('No set deadline');
            }
        },

        checklistPercentage() {
            return Math.floor(this.checklistData.done * 100 / this.checklistData.total)
        },

        statusColor() {
            switch (this.cloneCardData.status) {
                case 'active':
                    return 'green'
                case 'cancelled':
                    return 'red'
                case 'completed':
                    return 'blue'
                default:
                    return 'blue'
            }
        }
    },
};
</script>
