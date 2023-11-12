<script setup>
import { computed, nextTick, ref, watch } from 'vue';
const props = defineProps({
  board: Number,
  column: Object,
  vari: String
});
const columnTitle = computed(() => props.column?.title);
watch(
  () => props?.column,
  () => (cards.value = props?.column?.cards),
  () => (group_user.value = props?.vari)
);
</script>
<template>
    <div class="w-72 max-h-full bg-gray-200 flex flex-col rounded-md">
        <div class="flex items-center justify-between px-3 py-2">
            <h3 class="font-semibold text-sm text-gray-700">
                {{ columnTitle }}
            </h3>
        </div>
    </div>
    
</template>