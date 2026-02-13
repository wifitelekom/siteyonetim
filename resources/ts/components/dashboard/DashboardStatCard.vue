<script setup lang="ts">
interface Props {
  title: string
  value: string
  subtitle: string
  icon: string
  color: string
  iconBgColor?: string
}

withDefaults(defineProps<Props>(), {
  iconBgColor: undefined,
})
</script>

<template>
  <VCard>
    <VCardText class="d-flex align-center gap-4">
      <VAvatar
        :color="iconBgColor ?? color"
        variant="tonal"
        size="48"
        rounded
      >
        <VIcon
          :icon="icon"
          size="28"
          :color="color"
        />
      </VAvatar>

      <div>
        <p class="text-body-2 text-medium-emphasis mb-0">
          {{ title }}
        </p>
        <h5 class="text-h5 font-weight-bold">
          {{ value }}
        </h5>
        <span class="text-caption text-medium-emphasis">{{ subtitle }}</span>
      </div>
    </VCardText>
  </VCard>
</template>
