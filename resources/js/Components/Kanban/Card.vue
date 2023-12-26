<script setup>
import Datepicker from 'vue3-datepicker';
import { computed, nextTick, ref, reactive, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { PencilIcon } from '@heroicons/vue/24/solid';
import { TrashIcon } from '@heroicons/vue/24/solid';
import { useEditCard } from '@/Composables/useEditCard';
import ConfirmDialog from '@/Components/Kanban/ConfirmDialog.vue';
import Comment from '@/Components/Kanban/Comment.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Logger } from 'sass';
import moment from 'moment';
const props = defineProps({
  card: Object,
  boardTitle: String,
});
// TODO: Move to composable useModal
const isOpen = ref(false);
const board_title = ref(props.boardTitle);
const isDialogOpen = ref(false);
const panel = ref([0]);
const history = ref([]);
const columns = ref([]);
const emit = defineEmits(['onReloadColumns'])
const openModal = () => (isOpen.value = true);
const inputCardContentRef = ref();
const boardCreator = ref();

const closeModal = confirm => {
  isOpen.value = false;
  if (confirm) {
    router.delete(
      route('columns.cards.destroy', {
        card: props?.card?.id,
        column: props?.card?.column,
      })
    );
  }
};

const form = useForm({
  content: props?.card?.content,
});

const isEditing = computed(
  () => props?.card?.id === useEditCard?.value?.currentCard
);

const cardContent = computed(() => props.card?.content);
const assign_users = computed(() => props.card?.users);
const comments = computed(() => props.card?.comments);
const card_id = computed(() => props.card?.id);
const board_id = computed(() => props.card?.board_id);
const onSubmit = () => {
  form.post(
    route('columns.cards.update', {
      column: props?.card?.column,
      card: props?.card?.id,
    }),
    {
      onSuccess: () => {
        useEditCard.value.currentCard = null;
      },
    }
  );
};

const onCancel = () => {
  useEditCard.value.currentCard = null;
  form.reset();
};



//Details
// const openDetailModal = () => ();
// const closeDetailModal = () => (isDialogOpen.value = false);
const openDetailModal = () => {
  isDialogOpen.value = true;
  axios.post('/get-activities', {
    card_id: card_id.value,
    board_id: board_id.value,
  }).then((res) => {
    if (res.data.status == 'success') {
      history.value = res.data.data;
      columns.value = res.data.columns;
      selectedColumn.value = (props?.card?.column_id != null) ? props?.card?.column_id : '';
      boardCreator.value = res.data.board_user;
    }
  }).catch((error) => {

  })
};
const closeDetailModal = () => {
  isDialogOpen.value = false;
  emit('onReloadColumns');
};


//Autocomplete
const data = reactive({
  posts: [],
  term: ''
});

//Demo
const select = ref(null);
const keyword = ref('');
const selectedColumn = ref('');
const items = ref([]);
const loading = ref(false);
const columnDisabled = ref(false);
const start_date = ref((props?.card?.start_date != null) ? new Date(props?.card?.start_date) : null);
const end_date = ref((props?.card?.end_date != null) ? new Date(props?.card?.end_date) : null);
const due_date = ref((props?.card?.due_date != null) ? new Date(props?.card?.due_date) : null);

// 
select.value = assign_users.value[0];
const query = async (_keyword) => {
  if (_keyword != null && _keyword.length > 1) {
    loading.value = true;
    const response = await axios.post('/search-user', {
      query: _keyword
    });
    if (response.data.length) {
      items.value = response.data;
      loading.value = false;
    } else {
      data.message = 'Nothing found!';
    }
  }
};

watch(selectedColumn, (value) => {
  columnDisabled.value = true;
  if (value != null) {
    axios.post('/update-column', {
      card_id: card_id.value,
      column_id: value
    }).then((res) => {
      if (res.data.status == "success") {
        columnDisabled.value = false;
      }
    }).catch((error) => {
    })
  }
});
watch(select, (value) => {
  if (value !== null && typeof value === 'object') {
    axios.post('/assign-card', {
      user_id: value.id,
      card_id: card_id.value
    }).then((res) => {
      const response = res;
      router.reload();
    }).catch((error) => {
      console.log(error);
      console.log("error occurred");
    })

  }
});

watch(keyword, (v) => {
  query(v);
});

var getInitials = function (string) {
  var names = string.split(' '),
    initials = names[0].substring(0, 1).toUpperCase();

  if (names.length > 1) {
    initials += names[names.length - 1].substring(0, 1).toUpperCase();
  }
  return initials;
};

var padZeros = function (number, length) {
  var str = '' + number;
  while (str.length < length) {
    str = '0' + str;
  }
  return str;
};
var formatTaskID = function (ID) {
  var spanHTML = '';
  spanHTML = '<div class="pl-1 pr-1" style="display:inline-block;color:var(--ds-text-subtle,#7a869a);font-weight: 600;font-size: 10pt;line-height:10px"><span class="mdi mdi-checkbox-marked" style="font-size:20px;font-weight:700;padding-right:5px;color:#4BADE8;vertical-align:top;"></span>' + getInitials(board_title.value) + '' + padZeros(ID, 4) + '</div>';
  return spanHTML;
};
var formatDueDate = function (dueDate) {
  var spanHTML = '';
  const currentDate = new Date();
  const inputDate = new Date(dueDate);
  if (currentDate > inputDate) {
    spanHTML = '<div class="pl-1 pr-1" style="display:inline-block;background:var(--ds-background-danger, #FFEBE6);color:var(--ds-text-accent-red, #DE350B)"><span class="mdi mdi-calendar-month-outline" style="font-size:14px;font-weight:700;line-height:16px;padding-right:5px;"></span>' + moment(dueDate).format('D MMM YY') + '</div>';
  } else {
    spanHTML = '<div class="pl-1 pr-1" style="display:inline-block;background:var(--ds-background-neutral, #F4F5F7);color:var(--ds-text-subtle, #42526E);"><span class="mdi mdi-calendar-month-outline" style="font-size:14px;font-weight:700;line-height:16px;padding-right:5px;"></span>' + moment(dueDate).format('D MMM YY') + '</div>';

  }
  return spanHTML;
};
watch(start_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  var actual_start_date = year + '-' + month + '-' + day;
  axios.post('/update-card-details', { type: 'start_date', date: actual_start_date, card_id: card_id.value }).then((res) => {
    if (res.data.status == 'success') {

    }
  }).catch((error) => {
    // console.log(error);
  })
});
watch(end_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  var actual_end_date = year + '-' + month + '-' + day;
  axios.post('/update-card-details', { type: 'end_date', date: actual_end_date, card_id: card_id.value }).then((res) => {
    if (res.data.status == 'success') { }
  }).catch((error) => {
    // console.log(error);
  })
});
watch(due_date, (value) => {
  var date = new Date(value);
  const day = ('0' + date.getDate()).slice(-2);
  const month = date.getMonth() + 1;
  const year = date.getFullYear();
  var actual_due_date = year + '-' + month + '-' + day;
  axios.post('/update-card-details', { type: 'due_date', date: actual_due_date, card_id: card_id.value }).then((res) => {
    if (res.data.status == 'success') { }
  }).catch((error) => {
    // console.log(error);
  })
});


const editTaskDialog = ref(false);
const editTaskLoading = ref(false);
const editTask_filter_loading = ref(false);
const editTask_filter_items = ref([]);
const editTask_scan_date = ref();
const editTask_start_date = ref();
const editTask_end_date = ref();
const editTask_due_date = ref();
const editTask_filter_select = ref(null);
const editTask_keyword = ref('');
const editTaskForm = useForm({
  board_id: '',
  card_id: '',
  vulnerabilityName: '',
  vulnerabilityID: '',
  description: '',
  vulnerabilityDetails: '',
  asset_ip: '',
  ip_and_vuln_id: '',
  port: '',
  protocol: '',
  os_type: '',
  os_version: '',
  business_unit: '',
  class: '',
  cve_id: '',
  cvss_score: '',
  severity: '',
  solution: '',
  impact_of_vulnerability: '',
  scan_date_time: '',
  background: '',
  service: '',
  remediation: '',
  references: '',
  exception: '',
  tags: '',
  asset_version: '',
  model: '',
  make: '',
  asset_type: '',
  host_name: '',
  PLK_VLAN10_POS_SICOM_Subnet: '',
  PLK_VLAN70_Kiosk_Subnet: '',
  PLK_VLAN254_Meraki_Management_Subnet: '',
  PLK_VLAN4_Subnet: '',
  BK_VLAN10_POS_SICOM_Subnet: '',
  BK_VLAN70_Kiosk_Subnet: '',
  BK_VLAN254_Meraki_Management_Subnet: '',
  BK_VLAN4_Subnet: '',
  THS_VLAN10_POS_SICOM_Subnet: '',
  THS_VLAN70_Kiosk_Subnet: '',
  THS_VLAN254_Meraki_Management_Subnet: '',
  THS_VLAN4_Subnet: '',
  assignee: '',
  start_date: '',
  end_date: '',
  due_date: '',
});
watch(editTask_keyword, (v) => {
  editTask_filter_query(v);
});
const editTask_filter_query = async (query) => {
  if (query != null && query.length > 1) {
    editTask_filter_loading.value = true;
    const response = await axios.post('/search-user', {
      query: query
    });
    if (response.data.length) {
      editTask_filter_items.value = response.data;
      editTask_filter_loading.value = false;
    }
  }

};
const closeEditModal = () => {
  editTaskDialog.value = false;
};
const openEditModal = () => {
  editTaskDialog.value = true;
};
watch(editTask_start_date, (value) => {
  if (value != null) {
    var date = new Date(value);
    const day = ('0' + date.getDate()).slice(-2);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    editTaskForm.start_date = year + '-' + month + '-' + day;
  }
});
watch(editTask_end_date, (value) => {
  if (value != null) {
    var date = new Date(value);
    const day = ('0' + date.getDate()).slice(-2);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    editTaskForm.end_date = year + '-' + month + '-' + day;
  }
});
watch(editTask_due_date, (value) => {
  if (value != null) {
    var date = new Date(value);
    const day = ('0' + date.getDate()).slice(-2);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    editTaskForm.due_date = year + '-' + month + '-' + day;
  }
});
watch(editTask_scan_date, (value) => {
  if (value != null) {
    var date = new Date(value);
    const day = ('0' + date.getDate()).slice(-2);
    const month = date.getMonth() + 1;
    const year = date.getFullYear();
    editTaskForm.scan_date_time = year + '-' + month + '-' + day;
  }
});
const submitEditTask = () => {
  var value = editTask_filter_select.value;
  if (value !== null && typeof value === 'object') 
  {
    editTaskForm.assignee = value.id;
  }
  editTaskForm.post('/update-task', {
    preserveScroll: true,
    onStart: () => {editTaskLoading.value = true;},
    onSuccess: (val) => {
      editTaskForm.reset();
      editTask_filter_select.value = null;
      closeEditModal();
      // key_column.value = key_column.value ? false : true;
    },
    onError: (err) => {},
    onFinish: () => {
      editTaskLoading.value = false;
      emit('onReloadColumns');
    },
  });
}
const showForm = async (cardContent) => {
  editTaskForm.reset();
  editTaskForm.board_id = cardContent.board_id;
  editTaskForm.card_id = cardContent.id;
  editTaskForm.vulnerabilityName = cardContent.vulnerability_title;
  editTaskForm.vulnerabilityID = cardContent.vulnerability_id;
  editTaskForm.description = cardContent.content;
  editTaskForm.vulnerabilityDetails = cardContent.vulnerability_details;
  editTaskForm.ip_and_vuln_id = cardContent.ip_and_vuln_id;
  editTaskForm.asset_ip = cardContent.asset_ip;
  editTaskForm.port = cardContent.port;
  editTaskForm.protocol = cardContent.protocol;
  editTaskForm.os_type = cardContent.os_type;
  editTaskForm.os_version = cardContent.os_version;
  editTaskForm.business_unit = cardContent.business_unit;
  editTaskForm.class = cardContent.class;
  editTaskForm.cve_id = cardContent.cve_id;
  editTaskForm.cvss_score = cardContent.cvss_score;
  editTaskForm.severity = cardContent.severity;
  editTaskForm.solution = cardContent.solution;
  editTaskForm.impact_of_vulnerability = cardContent.impact_of_vulnerability;
  editTaskForm.scan_date_time = cardContent.scan_date_time;
  editTask_scan_date.value = new Date(cardContent.scan_date_time);
  editTaskForm.background = cardContent.background;
  editTaskForm.service = cardContent.service;
  editTaskForm.remediation = cardContent.remediation;
  editTaskForm.references = cardContent.references;
  editTaskForm.exception = cardContent.exception;
  editTaskForm.tags = cardContent.tags;
  editTaskForm.asset_version = cardContent.asset_version;
  editTaskForm.model = cardContent.model;
  editTaskForm.make = cardContent.make;
  editTaskForm.host_name = cardContent.host_name;
  editTaskForm.asset_type = cardContent.asset_type;
  editTaskForm.PLK_VLAN10_POS_SICOM_Subnet = cardContent.PLK_VLAN10_POS_SICOM_Subnet;
  editTaskForm.PLK_VLAN70_Kiosk_Subnet = cardContent.PLK_VLAN70_Kiosk_Subnet;
  editTaskForm.PLK_VLAN254_Meraki_Management_Subnet = cardContent.PLK_VLAN254_Meraki_Management_Subnet;
  editTaskForm.PLK_VLAN4_Subnet = cardContent.PLK_VLAN4_Subnet;
  editTaskForm.BK_VLAN70_Kiosk_Subnet = cardContent.BK_VLAN70_Kiosk_Subnet;
  editTaskForm.BK_VLAN10_POS_SICOM_Subnet = cardContent.BK_VLAN10_POS_SICOM_Subnet;
  editTaskForm.BK_VLAN254_Meraki_Management_Subnet = cardContent.BK_VLAN254_Meraki_Management_Subnet;
  editTaskForm.BK_VLAN4_Subnet = cardContent.BK_VLAN4_Subnet;
  editTaskForm.THS_VLAN10_POS_SICOM_Subnet = cardContent.THS_VLAN10_POS_SICOM_Subnet;
  editTaskForm.THS_VLAN70_Kiosk_Subnet = cardContent.THS_VLAN70_Kiosk_Subnet;
  editTaskForm.THS_VLAN254_Meraki_Management_Subnet = cardContent.THS_VLAN254_Meraki_Management_Subnet;
  editTaskForm.THS_VLAN4_Subnet = cardContent.THS_VLAN4_Subnet;
  editTaskForm.start_date = cardContent.start_date;
  editTaskForm.end_date = cardContent.end_date;
  editTaskForm.due_date = cardContent.due_date;
  editTask_start_date.value = cardContent.start_date != null ? new Date(cardContent.start_date): null;
  editTask_end_date.value = cardContent.end_date != null? new Date(cardContent.end_date): null;
  editTask_due_date.value = cardContent.due_date != null? new Date(cardContent.due_date): null;
  if(cardContent.users.length == 1)
  {
    var assignee = cardContent.users[0];
    editTask_filter_select.value = assignee.id;
    editTask_filter_items.value = cardContent.users;
  }
  await nextTick();
  openEditModal();
};

</script>

<template>
  <div @click="openDetailModal"
    class="cursor-move relative group p-2 bg-white shadow rounded-md border border-b border-gray-300 hover:bg-gray-50">
    <form v-if="isEditing" @keydown.esc="onCancel" @submit.prevent="onSubmit">
      <textarea v-model="form.content" type="text" @keydown.enter.prevent="onSubmit" placeholder="Card content ..."
        ref="inputCardContentRef" rows="3"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
      </textarea>
      <div class="mt-2 space-x-2">
        <button type="submit"
          class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
          Save card
        </button>
        <button @click.prevent="onCancel" type="button"
          class="inline-flex items-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-gray-700 hover:text-black focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
          Cancel
        </button>
      </div>
    </form>
    <div v-else>
      <p class="text-sm">{{ cardContent }}</p>
      <v-row class="pb-3">
        <v-col cols="12" class="pa-0 pt-2 pl-2">
          <div class="text-lg-left left-1 pt-2"
            style="font-size: 11px;font-weight: 700;line-height: 16px;text-align: left;" v-if="card.due_date != null"
            v-html="formatDueDate(card.due_date)">
          </div>
        </v-col>
        <v-col cols="6" class="pa-0 pl-2">
          <div class="text-lg-left left-1 pt-2" v-html="formatTaskID(card.id)"></div>
        </v-col>
        <v-col cols="6" class="pb-0">
          <div class="text-lg-right right-1">
            <template v-for="user in assign_users" :key="user.id">
              <v-avatar color="blue" style="width:22px;height:22px;font-size:10px;float:right;">
                {{ getInitials(user.name) }}
              </v-avatar>
            </template>
          </div>
        </v-col>
      </v-row>
      <!-- @click.prevent="showForm" -->
      <div class="hidden absolute right-1 inset-0 group-hover:flex justify-end space-x-2 items-center">
        <button @click.stop.prevent="showForm(card)"
          class="w-8 h-8 bg-gray-50 text-gray-600 hover:text-black hover:bg-gray-200 rounded-md grid place-content-center">
          <PencilIcon class="w-5 h-5" />
        </button>
        <button @click.stop.prevent="openModal"
          class="w-8 h-8 bg-gray-50 text-red-600 hover:text-red-700 hover:bg-gray-200 rounded-md grid place-content-center">
          <TrashIcon class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>

  <ConfirmDialog :show="isOpen" @confirm="closeModal($event)" title="Remove Card"
    message="Are you sure you want to delete this card?" />

  <v-dialog v-model="isDialogOpen" width="1200" height="800">
    <v-card class="h-full">
      <v-card-title>
        {{ cardContent }}
      </v-card-title>
      <v-card-text>
        <v-row>
          <v-col cols="8" class="overflow-x-auto" style="height: 570px;">
            <v-row>
              <v-col cols="6">
                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Vulnerability Name :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.content }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Vulnerability ID :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.vulnerability_id }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Vulnerability Description :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.vulnerability_desc }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Vulnerability Details :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.vulnerability_details }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Asset IP :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.asset_ip }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">IP & Vuln Id :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.ip_and_vuln_id }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Port :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.port }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Protocol :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.protocol }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">OS Type/Version :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.os_type }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">OS Version :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.os_version }}</v-col>
                </v-row>
                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Business Unit :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.business_unit }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Class :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.class }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">CVE ID (Common Vulnerabilities and Exposures ID)
                      :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.cve_id }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">CVSS Score (Common Vulnerability Scoring System
                      Score) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.cvss_score }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Severity :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.severity }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Solution :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.solution }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Impact Of Vulnerability :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.impact_of_vulnerability }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Scan Date Time :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.scan_date_time }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Background :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.background }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Service :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.service }}</v-col>
                </v-row>
              </v-col>
              <v-col cols="6">
                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Remediation :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.remediation }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">References :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.references }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Exception :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.exception }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Tags :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.tags }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Asset Version :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.asset_version }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Model :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.model }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Make :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.make }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Asset Type :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.asset_type }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">Host Name :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.host_name }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">PLK_VLAN10 (POS-SICOM) Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.PLK_VLAN10_POS_SICOM_Subnet }}</v-col>
                </v-row>
                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">PLK_VLAN70 (Kiosk) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.PLK_VLAN70_Kiosk_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">PLK_VLAN254 (Meraki-Management) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.PLK_VLAN254_Meraki_Management_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">PLK_VLAN4 Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.PLK_VLAN4_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">BK_VLAN10 (POS-SICOM) Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.BK_VLAN10_POS_SICOM_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">BK_VLAN70 (Kiosk) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.BK_VLAN70_Kiosk_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">BK_VLAN254 (Meraki-Management) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.BK_VLAN254_Meraki_Management_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">BK_VLAN4 Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.BK_VLAN4_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">THS_VLAN10 (POS-SICOM) Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.THS_VLAN10_POS_SICOM_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">THS_VLAN70 (Kiosk) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.THS_VLAN70_Kiosk_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">THS_VLAN254 (Meraki-Management) :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.THS_VLAN254_Meraki_Management_Subnet }}</v-col>
                </v-row>

                <v-row style="font-size: 14px;">
                  <v-col cols="6" class="pa-0"><b class="accent--text">THS_VLAN4 Subnet :</b></v-col>
                  <v-col cols="6" class="pa-0">{{ card.THS_VLAN4_Subnet }}</v-col>
                </v-row>
              </v-col>
              <v-col cols="12">
                <Comment :cardID=card_id :allcomments="comments" :allhistory="history" />
              </v-col>
            </v-row>
          </v-col>
          <v-col cols="4">
            <v-row>
              <v-col cols="6">
                <v-select v-model="selectedColumn" :items="columns" item-title="title" item-value="id" density="compact"
                  variant="outlined" :disabled="columnDisabled"></v-select>
              </v-col>
            </v-row>

            <v-expansion-panels v-model="panel">
              <v-expansion-panel class="pa-0">
                <v-expansion-panel-title class="pa-2"
                  style="min-height: 35px; !important;border:1px solid #DFE1E6; background:#ffffff">Details</v-expansion-panel-title>
                <v-expansion-panel-text class="pa-0">
                  <v-row>
                    <v-col cols="3" class="pl-0 pt-3 pb-3">
                      <b class="accent--text" style="font-size:14px">Assignee</b>
                    </v-col>
                    <v-col cols="9" class="pl-0 pt-3 pb-3">
                      <v-combobox variant="outlined" density="compact" clearable chips color="green " v-model:search="keyword" no-filter
                        v-model="select" :items="items" :loading="loading" item-title="name" item-value="id" return-object
                        @focus="() => query(keyword)" />
                    </v-col>
                    <v-col cols="3" class="pl-0 pt-3 pb-3">
                      <b class="accent--text" style="font-size:14px">Start Date</b>
                    </v-col>
                    <v-col cols="9" class="pl-0 pt-3 pb-3">
                      <Datepicker  style="border: 1px solid lightgrey;" :clearable="true" v-model="start_date">
                        <template v-slot:clear="{ onClear }">
                          <v-chip @click="onClear" style="color: red;left:-35px !important;">x</v-chip>
                        </template>
                      </Datepicker>
                    </v-col>
                    <v-col cols="3" class="pl-0 pt-3 pb-3">
                      <b class="accent--text" style="font-size:14px">End Date</b>
                    </v-col>
                    <v-col cols="9" class="pl-0 pt-3 pb-3">
                      <Datepicker style="border: 1px solid lightgrey;" :clearable="true" v-model="end_date">
                        <template v-slot:clear="{ onClear }">
                          <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                        </template>
                      </Datepicker>
                    </v-col>
                    <v-col cols="3" class="pl-0 pt-3 pb-3">
                      <b class="accent--text" style="font-size:14px">Due Date</b>
                    </v-col>
                    <v-col cols="9" class="pl-0 pt-3 pb-3">
                      <Datepicker style="border: 1px solid lightgrey;" :clearable="true" v-model="due_date">
                        <template v-slot:clear="{ onClear }">
                          <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                        </template>
                      </Datepicker>
                    </v-col>
                    <v-col cols="12" class="pl-0 pt-3 pb-3" v-if="boardCreator">
                      Reporter<br>
                      <v-avatar color="blue" style="width:22px;height:22px;font-size:10px;float:right;">
                        {{ getInitials(boardCreator.name) }}
                      </v-avatar>
                      {{ boardCreator.name }}
                    </v-col>
                  </v-row>
                </v-expansion-panel-text>
              </v-expansion-panel>
            </v-expansion-panels>
            <v-row class="pt-4 pl-3">
              <v-col cols="12" class="pa-0 pb-1" v-if="card.created_at">
                <span style="font-size: 14px;color: #333;">Created {{ moment(card.updated_at).format('MMMM D, YYYY') }} at
                  {{ moment(card.updated_at).format('hh:mm A') }}</span>
              </v-col>
              <v-col cols="12" class="pa-0" v-if="card.updated_at">
                <span style="font-size: 14px;color: #333;">Updated {{ moment(card.updated_at).format('MMMM D, YYYY') }} at
                  {{ moment(card.updated_at).format('hh:mm A') }}</span>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn variant="elevated" color="primary" size="small" @click="closeDetailModal">Save & Close</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>

  <v-dialog persistent width="800" v-model="editTaskDialog">
    <form @submit.prevent="submitEditTask">
      <v-card title="Edit Task">
        <v-card-text>
          <v-row>
            <v-col cols="6">
              <div>
                <InputLabel value="Vulnerability Name*" />
                <v-text-field variant="outlined" density="compact" class="mt-2" v-model="editTaskForm.vulnerabilityName"
                  hide-details required></v-text-field>
                <InputError v-if="form.errors" class="mt-2" :message="editTaskForm.errors.vulnerabilityName" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Vulnerability ID*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.vulnerabilityID" hide-details></v-text-field>
                <InputError v-if="form.errors" class="mt-2" :message="editTaskForm.errors.vulnerabilityID" />
              </div>
            </v-col>
            <v-col cols="6" class="pt-0">
              <div>
                <InputLabel value="Vulnerability Description*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.description" hide-details></v-text-field>
                <InputError v-if="form.errors" class="mt-2" :message="editTaskForm.errors.description" />
              </div>
            </v-col>
            <v-col cols="6" class="pt-0">
              <div>
                <InputLabel value="Vulnerability Details*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.vulnerabilityDetails" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.vulnerabilityDetails" />
              </div>
            </v-col>
            <v-col cols="6" class="pt-0">
              <div>
                <InputLabel value="Asset IP*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.asset_ip"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.asset_ip" />
              </div>
            </v-col>
            <v-col cols="6" class="pt-0">
              <div>
                <InputLabel value="IP & Vuln Id*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.ip_and_vuln_id" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.ip_and_vuln_id" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Port*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.port"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.port" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Protocol*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.protocol"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.protocol" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="OS Type/Version*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.os_type"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.os_type" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Os Version*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.os_version"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.os_version" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Business Unit*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.business_unit" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.business_unit" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Class*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.class"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.class" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="CVE ID*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.cve_id"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.cve_id" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="CVSS Score*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.cvss_score"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.cvss_score" />
              </div>
            </v-col>
            <v-col cols="6" class="pb-0">
              <div>
                <InputLabel value="Severity*" />
                <v-select variant="outlined" density="compact" class="mt-2" required v-model="editTaskForm.severity"
                  :items="['Critical', 'High', 'Medium', 'Low', 'Informational', 'None']"></v-select>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.severity" />
              </div>
            </v-col>
            <v-col cols="6" class="pb-0">
              <div>
                <InputLabel value="Solution*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.solution"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.solution" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Impact Of Vulnerabilty*" />
                <v-text-field variant="outlined" density="compact" class="mt-2" required
                  v-model="editTaskForm.impact_of_vulnerability" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.impact_of_vulnerability" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Scan Date Time*" />
                <Datepicker class="w-100 mt-2" variant="outlined" density="compact"
                  style="border: 1px solid #6b7280;border-radius:5px;" v-model="editTask_scan_date">
                  <template v-slot:clear="{ onClear }">
                    <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                  </template>
                </Datepicker>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.scan_date_time" />

              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Background*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.background"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.background" />

              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Service*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.service"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.service" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Remediation*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.remediation" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.remediation" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="References*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.references"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.references" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Exception*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.exception"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.exception" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Tags*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.tags"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.tags" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Asset Version*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.asset_version" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.asset_version" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Model*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.model"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.model" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Make*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.make"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.make" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Asset Type*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.asset_type"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.asset_type" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="Host Name*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2" v-model="editTaskForm.host_name"
                  hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.host_name" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="PLK_VLAN10 (POS-SICOM) Subnet*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.PLK_VLAN10_POS_SICOM_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.PLK_VLAN10_POS_SICOM_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="PLK_VLAN70 (Kiosk)*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.PLK_VLAN70_Kiosk_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.PLK_VLAN70_Kiosk_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="PLK_VLAN254 (Meraki-Management)*" />
                <v-text-field variant="outlined" density="compact" class="mt-2" required
                  v-model="editTaskForm.PLK_VLAN254_Meraki_Management_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.PLK_VLAN254_Meraki_Management_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="PLK_VLAN4 Subnet*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.PLK_VLAN4_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.PLK_VLAN4_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="BK_VLAN10 (POS-SICOM) Subnet*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.BK_VLAN10_POS_SICOM_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.BK_VLAN10_POS_SICOM_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="BK_VLAN70 (Kiosk)*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.BK_VLAN70_Kiosk_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.BK_VLAN70_Kiosk_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="BK_VLAN254 (Meraki-Management)*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.BK_VLAN254_Meraki_Management_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.BK_VLAN254_Meraki_Management_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="BK_VLAN4 Subnet*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.BK_VLAN4_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.BK_VLAN4_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="THS_VLAN10 (POS-SICOM) Subnet*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.THS_VLAN10_POS_SICOM_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.THS_VLAN10_POS_SICOM_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="THS_VLAN70 (Kiosk)*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.THS_VLAN70_Kiosk_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.THS_VLAN70_Kiosk_Subnet" />
              </div>
            </v-col>
            <v-col cols="6">
              <div>
                <InputLabel value="THS_VLAN254 (Meraki-Management)*" />
                <v-text-field variant="outlined" density="compact" required class="mt-2"
                  v-model="editTaskForm.THS_VLAN254_Meraki_Management_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2"
                  :message="editTaskForm.errors.THS_VLAN254_Meraki_Management_Subnet" />

              </div>
            </v-col>
            <v-col cols="6" class="pb-0">
              <div>
                <InputLabel value="THS_VLAN4 Subnet*" />
                <v-text-field variant="outlined" required density="compact" class="mt-2"
                  v-model="editTaskForm.THS_VLAN4_Subnet" hide-details></v-text-field>
                <InputError v-if="editTaskForm.errors" class="mt-2" :message="editTaskForm.errors.THS_VLAN4_Subnet" />
              </div>
            </v-col>
            <v-col cols="6" class="pb-0">
              <div>
                <InputLabel value="Assignee Name" />
                <v-combobox variant="outlined" density="compact" clearable chips color="green "
                  v-model:search="editTask_keyword" no-filter v-model="editTask_filter_select" :items="editTask_filter_items"
                  :loading="editTask_filter_loading" item-title="name" item-value="id" class="mt-2"
                @focus="() => editTask_filter_query(keyword)" />
            </div>
          </v-col>
          <v-col cols="6" class="pt-0">
            <div>
              <InputLabel value="Start Date" />
              <Datepicker variant="outlined" required density="compact"
                style="border: 1px solid #6b7280;border-radius:5px;" class="mt-2 w-100" v-model="editTask_start_date">
                <template v-slot:clear="{ onClear }">
                  <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                </template>
              </Datepicker>
            </div>
          </v-col>
          <v-col cols="6" class="pt-0">
            <div>
              <InputLabel value="End Date" />
              <Datepicker variant="outlined" required density="compact" class="mt-2 w-100"
                style="border: 1px solid #6b7280;border-radius:5px;" v-model="editTask_end_date">
                <template v-slot:clear="{ onClear }">
                  <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                </template>
              </Datepicker>
            </div>
          </v-col>
          <v-col cols="6">
            <div>
              <InputLabel value="Due Date" />
              <Datepicker variant="outlined" class="mt-2 w-100" required density="compact"
                style="border: 1px solid #6b7280;border-radius:5px;" v-model="editTask_due_date">
                <template v-slot:clear="{ onClear }">
                  <v-chip @click="onClear" style="color: red;left:-35px !important">x</v-chip>
                </template>
              </Datepicker>
            </div>
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn size="small" variant="outlined" color="red-darken-4" text="Close" :loading="editTaskLoading"
          :disabled="editTaskLoading" @click="closeEditModal"></v-btn>
        <v-btn size="small" variant="outlined" color="green-darken-3" :loading="editTaskLoading"
          :disabled="editTaskLoading" text="Submit" type="submit"></v-btn>
      </v-card-actions>
    </v-card>
  </form>
  </v-dialog>
</template>
