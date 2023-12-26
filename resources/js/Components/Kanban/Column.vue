<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import cloneDeep from 'lodash.clonedeep';
import Draggable from 'vuedraggable';
import Card from '@/Components/Kanban/Card.vue';
import CardCreate from '@/Components/Kanban/CardCreate.vue';
import ConfirmDialog from '@/Components/Kanban/ConfirmDialog.vue';
import MenuDropDown from '@/Components/Kanban/MenuDropDown.vue';

const props = defineProps({
    boardId: Number,
    boardtitle: String,
    filters: Object,
});
const emit = defineEmits(['reorder-commit', 'reorder-change']);
const isOpen = ref(false);
const board_id = ref(props.boardId);
const board_title = ref(props.boardtitle);
const filtersData = ref(props.filters);
const openModal = () => (isOpen.value = true);
const columns = ref([]);
const cards = ref([]);
const menuItems = [
    {
        name: 'Delete column',
        action: () => openModal(),
    },
];
const closeModal = (val, colID) => {
    isOpen.value = false;
    if (val) {
        router.delete(route('columns.destroy', { column: colID }));
        board_id.value = null;
        board_id.value = props.boardId;
    }
};
watch(board_id, (value) => {
   
    var postData = {};
    if(filtersData.value.length == 0)
    {
        var postData = {
            board_id : board_id.value,
        };
    }else{
        var postData = filtersData.value;
    }
   
    axios.post('/get-cards-based-on-filters', postData).then((res) => {
        if (res.data.status == 'success') {
            columns.value = [];
            columns.value = res.data.data;
            nextTick();
        }
    }).catch((error) => {
        // console.log(error);
    });
}, {
    immediate: true,
    deep: true,
});
const onCardCreated = async () => {
  // scroll to the bottom of the list
  await nextTick();
  cardsRef.value.scrollTop = cardsRef.value.scrollHeight;
};
const onReorderCards = (ev,colID) => {
    if(ev.added)
    {
        var element = ev.added.element;
        var newIndex = ev.added.newIndex* 1000 + 1000;
        var customobj = [{column_id:colID,card_id:element.id,position:newIndex}];
        router.put(route('cards.reorder'), {
            columns: customobj,
        });
    }
};

const onReorderEnds = (ev) => {
   
};
const reloadColumns = (obj) => {
    board_id.value = null;
    board_id.value = props.boardId;
}
</script>

<template>
    <div v-for="(column, index) in columns">
        <div class="w-72 max-h-full bg-gray-100 flex flex-col rounded-md">
            <div class="flex items-center justify-between px-3 py-2">
                <h3 class="font-semibold text-sm text-gray-700">
                    {{ column.title }}
                </h3>
                <MenuDropDown :menuItems="menuItems" />
            </div>
            <div class="pb-3 flex-1 flex flex-col overflow-hidden" >
                <div class="px-3 overflow-y-auto" ref="cardsRef">
                    <Draggable v-model="column.cards" group="cards" item-key="id" tag="ul" drag-class="drag" ghost-class="ghost"
                        class="space-y-3" @change="onReorderCards($event,column.id)" @end="onReorderEnds($event)">
                        <template #item="{ element }">
                        <li>
                            <Card :boardTitle="board_title" :card="element" @onReloadColumns="reloadColumns"/>
                        </li>
                        </template>
                    </Draggable>
                    <div class="px-3 mt-3">
                        <!-- <CardCreate :column="column" @created="onCardCreated" /> -->
                    </div>
                </div>
            </div>
        </div>
        <ConfirmDialog :show="isOpen" @confirm="closeModal($event, column.id)" title="Remove Column"
            message="Are you sure you want to delete this column and all its cards?" />
    </div>
</template>