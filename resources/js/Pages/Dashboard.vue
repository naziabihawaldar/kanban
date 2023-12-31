<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import moment from 'moment';

const props = defineProps({
  totalUsers: '',
  totalProjects: '',
  totalTask: '',
  overdueTask: '',
  barchart: Object,
  pieChart: Object,
  horizontalChart: Object,
  donutChart: Object,
  projectList: Object,
  taskList: Object,
});
const Taskheaders = [
  { "title": "Task", "key": "task", "align": "left", sortable: false },
  { "title": "Project", "key": "project", "align": "left", sortable: false },
  { "title": "Due Date", "key": "duedate", "align": "left", sortable: false },
  { "title": "Status", "key": "status", "align": "left", sortable: false },

];
const Projheaders = [
  { "title": "Project", "key": "project", "align": "left", sortable: false },
  { "title": "Created At", "key": "created_at", "align": "left", sortable: false },
  { "title": "Created By", "key": "created_by", "align": "left", sortable: false },

];
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>

    <v-container>
      <v-row no-gutters>
        <v-col cols="3">
          <v-card class="ma-2 pa-3">
            <h2 style="text-align: center;font-size: medium;font-weight: bold;color:#ED1941">Total Users</h2>
            <div style="text-align: center;padding-top: 7px;font-size: 24px;font-weight: 500;">{{ totalUsers }}</div>
          </v-card>
        </v-col>
        <v-col cols="3">
          <v-card class="ma-2 pa-3">
            <h2 style="text-align: center;font-size: medium;font-weight: bold;color:#ED1941">Total Projects</h2>
            <div style="text-align: center;padding-top: 7px;font-size: 24px;font-weight: 500;">{{ totalProjects }}</div>
          </v-card>
        </v-col>
        <v-col cols="3">
          <v-card class="ma-2 pa-3">
            <h2 style="text-align: center;font-size: medium;font-weight: bold;color:#ED1941">Total Tasks</h2>
            <div style="text-align: center;padding-top: 7px;font-size: 24px;font-weight: 500;">{{ totalTask }}</div>
          </v-card>
        </v-col>
        <v-col cols="3">
          <v-card class="ma-2 pa-3">
            <h2 style="text-align: center;font-size: medium;font-weight: bold;color:#ED1941">Over Due Tasks</h2>
            <div style="text-align: center;padding-top: 7px;font-size: 24px;font-weight: 500;">{{ overdueTask }}</div>
          </v-card>
        </v-col>
      </v-row>
      <v-row no-gutters>
        <v-col cols="6">
          <v-card class="ma-2 pa-3">
            <apexchart :width="horizontalChart.width" :height="horizontalChart.height" :type="horizontalChart.type"
              :options="horizontalChart.options" :series="horizontalChart.series"></apexchart>
          </v-card>

        </v-col>
        <v-col cols="6">
          <v-card class="ma-2 pa-3">
            <apexchart :width="pieChart.width" :height="pieChart.height" :type="pieChart.type" :options="pieChart.options"
              :series="pieChart.series"></apexchart>
          </v-card>
        </v-col>
      </v-row>
      <v-row no-gutters>
        <v-col cols="4">
          <v-card class="ma-2 pa-3">
            <h3 style="font-size: medium;font-weight: bold;color:#ED1941">Recent Projects List</h3>
            <v-data-table :headers="Projheaders" :items="projectList" disable-initial-sort item-key="title"
              :items-per-page="0">
              <template v-slot:item="item">
                <tr>
                  <td>{{ item.item.title }}</td>
                  <td>{{ moment(item.item.created_at).format('MMM D, YYYY') }}</td>
                  <td>
                    <span v-if="item.item.user">
                     {{ item.item.user.name }}
                    </span>
                    <span v-else>-</span>
                  </td>
                </tr>
              </template>

              <template #bottom></template>
            </v-data-table>
          </v-card>

        </v-col>
        <v-col cols="8">
          <v-card class="ma-2 pa-3">
            <h3 style="font-size: medium;font-weight: bold;color:#ED1941">Recent Tasks List</h3>
            <v-data-table :headers="Taskheaders" :items="taskList" disable-initial-sort item-key="title"
              :items-per-page="0">
              <template v-slot:item="item">
                <tr>
                  <td>{{ item.item.content }}</td>
                  <td>
                    <span v-if="item.item.board">
                     {{ item.item.board.title }}
                    </span>
                    <span v-else>-</span>
                  </td>
                  <td><span v-if="moment(item.item.due_date).isValid()">{{
                                    moment(item.item.due_date).format('D MMM YY') }}</span><span v-else> - </span></td>
                  <td>
                    <span v-if="item.item.task_status">
                      <v-chip size="small" color="red">Overdue</v-chip>
                    </span>
                    <span v-else>-</span>
                  </td>

                </tr>
              </template>
              <template #bottom></template>
            </v-data-table>
          </v-card>

        </v-col>
      </v-row>
    </v-container>
  </AuthenticatedLayout>
</template>
