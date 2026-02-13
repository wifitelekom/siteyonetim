<script setup lang="ts">
import { useTheme } from 'vuetify'

interface Props {
  rate: number
}

const props = defineProps<Props>()

const vuetifyTheme = useTheme()

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors

  return {
    chart: {
      type: 'radialBar' as const,
      parentHeightOffset: 0,
      sparkline: { enabled: true },
    },
    plotOptions: {
      radialBar: {
        startAngle: -90,
        endAngle: 90,
        hollow: { size: '60%' },
        track: {
          background: `rgba(${currentTheme['on-surface']}, 0.08)`,
          strokeWidth: '100%',
        },
        dataLabels: {
          name: {
            offsetY: -10,
            fontSize: '14px',
            color: currentTheme['on-surface'],
          },
          value: {
            offsetY: -2,
            fontSize: '24px',
            fontWeight: 700,
            color: currentTheme['on-surface'],
            formatter: (val: number) => `%${Math.round(val)}`,
          },
        },
      },
    },
    colors: [props.rate >= 70 ? currentTheme.success : props.rate >= 40 ? currentTheme.warning : currentTheme.error],
    labels: ['Tahsilat Orani'],
    stroke: { lineCap: 'round' as const },
  }
})

const series = computed(() => [props.rate])
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>Bu Ay Tahsilat</VCardTitle>
      <VCardSubtitle>Aylik tahsilat performansi</VCardSubtitle>
    </VCardItem>

    <VCardText class="d-flex justify-center">
      <VueApexCharts
        type="radialBar"
        height="230"
        :options="chartOptions"
        :series="series"
      />
    </VCardText>
  </VCard>
</template>
