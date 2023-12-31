<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
const headers = [
    { "title": "Vulnerability Name", "key": "vulnerabilityName", "align": "left", sortable: false },
    { "title": "Vulnerability ID", "key": "vulnerabilityID", "align": "left", sortable: false },
    { "title": "Vulnerability Description", "key": "vulnerabilityDescription", "align": "left", sortable: false },
    { "title": "Project", "key": "project", "align": "left", sortable: false },
    { "title": "Assigned To", "key": "assignedTo", "align": "left", sortable: false },
    { "title": "Start Date", "key": "start_date", "align": "left", sortable: false },
    { "title": "End Date", "key": "end_date", "align": "left", sortable: false },
    { "title": "Due Date", "key": "due_date", "align": "left", sortable: false },
    { "title": "Status", "key": "due_date", "align": "left", sortable: false },
];
</script>
<template>
    <AuthenticatedLayout>
        <v-container>
            <v-row no-gutters>
                <v-col cols="8">
                    <h2 class="font-black text-xl text-gray-800 leading-tight">Reports</h2>
                </v-col>
                <v-col cols="12" class="mt-3">
                    <v-table>
                        <thead>
                            <tr>
                                <th class="text-left">
                                    Name
                                </th>
                                <th class="text-left">
                                    Description
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><Link href="/consolidated-reports">Consolidated Report</Link></td>
                                <td>N/A</td>
                            </tr>
                            <tr>
                                <td><Link href="/reports">Detailed Report</Link></td>
                                <td>N/A</td>
                            </tr>
                        </tbody>

                    </v-table>
                </v-col>
            </v-row>
        </v-container>
    </AuthenticatedLayout>
</template>