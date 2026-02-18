<script setup lang="ts">
import { useTheme } from 'vuetify'

interface DailyItem {
  date: string
  income: number
  expense: number
}

interface Props {
  data: DailyItem[]
}

const props = defineProps<Props>()

const vuetifyTheme = useTheme()

const chartOptions = computed(() => {
  const currentTheme = vuetifyTheme.current.value.colors

  return {
    chart: {
      type: 'bar' as const,
      parentHeightOffset: 0,
      toolbar: { show: false },
      stacked: false,
    },
    plotOptions: {
      bar: {
        borderRadius: 3,
        columnWidth: '55%',
      },
    },
    colors: [currentTheme.success, currentTheme.error],
    dataLabels: { enabled: false },
    stroke: {
      show: true,
      width: 1,
      colors: ['transparent'],
    },
    xaxis: {
      categories: props.data.map(d => d.date),
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: {
        style: {
          colors: currentTheme['on-surface'],
          fontSize: '10px',
        },
        rotate: -45,
        rotateAlways: true,
      },
    },
    yaxis: {
      labels: {
        style: {
          colors: currentTheme['on-surface'],
          fontSize: '11px',
        },
        formatter: (val: number) => {
          if (val >= 1000)
            return `${(val / 1000).toFixed(0)}K`
          return val.toString()
        },
      },
    },
    grid: {
      borderColor: `rgba(${currentTheme['on-surface']}, 0.12)`,
      strokeDashArray: 4,
    },
    legend: {
      position: 'top' as const,
      horizontalAlign: 'left' as const,
      labels: {
        colors: currentTheme['on-surface'],
      },
      itemMargin: { horizontal: 10 },
    },
    tooltip: {
      y: {
        formatter: (val: number) => new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val),
      },
    },
  }
})

const series = computed(() => [
  { name: 'Tahsilat', data: props.data.map(d => d.income) },
  { name: 'Odeme', data: props.data.map(d => d.expense) },
])
</script>

<template>
  <VCard>
    <VCardItem>
      <VCardTitle>30 Gunluk Nakit Akisi</VCardTitle>
      <VCardSubtitle>Son 30 gundeki tahsilat ve odeme hareketleri</VCardSubtitle>
    </VCardItem>

    <VCardText>
      <VueApexCharts
        type="bar"
        height="300"
        :options="chartOptions"
        :series="series"
      />
    </VCardText>
  </VCard>
</template>
