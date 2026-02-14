<script setup lang="ts">
interface TimelineItem {
  uid: string
  date: string
  date_display: string
  type: 'receivable' | 'payable' | 'past_receipt'
  title: string
  subtitle: string
  amount: string
  dot_class: string
}

interface Props {
  items: TimelineItem[]
}

defineProps<Props>()

const getTimelineColor = (type: string) => {
  switch (type) {
    case 'receivable': return 'success'
    case 'payable': return 'error'
    case 'past_receipt': return 'info'
    default: return 'secondary'
  }
}

const getTimelineIcon = (type: string) => {
  switch (type) {
    case 'receivable': return 'ri-arrow-down-line'
    case 'payable': return 'ri-arrow-up-line'
    case 'past_receipt': return 'ri-check-line'
    default: return 'ri-time-line'
  }
}
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>{{ $t('dashboard.timeline.title') }}</VCardTitle>
      <VCardSubtitle>{{ $t('dashboard.timeline.subtitle') }}</VCardSubtitle>
    </VCardItem>

    <VCardText>
      <VTimeline
        density="compact"
        align="start"
        side="end"
        truncate-line="both"
      >
        <VTimelineItem
          v-for="item in items.slice(0, 8)"
          :key="item.uid"
          :dot-color="getTimelineColor(item.type)"
          size="x-small"
        >
          <div class="d-flex align-center justify-space-between mb-1">
            <div class="d-flex align-center gap-2">
              <VIcon
                :icon="getTimelineIcon(item.type)"
                :color="getTimelineColor(item.type)"
                size="16"
              />
              <span class="text-body-2 font-weight-medium">{{ item.title }}</span>
            </div>
            <span class="text-caption text-medium-emphasis">{{ item.date_display }}</span>
          </div>
          <div class="d-flex align-center justify-space-between">
            <span class="text-caption text-medium-emphasis">{{ item.subtitle }}</span>
            <span class="text-body-2 font-weight-bold">{{ item.amount }}</span>
          </div>
        </VTimelineItem>
      </VTimeline>

      <div
        v-if="items.length === 0"
        class="text-center text-medium-emphasis py-6"
      >
        {{ $t('dashboard.timeline.empty') }}
      </div>
    </VCardText>
  </VCard>
</template>
