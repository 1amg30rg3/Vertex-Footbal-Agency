<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { cloneForForm } from '@/Support/clone';
import { scrollToFirstError } from '@/Support/form';
import { useRoute } from '@/Support/ziggy';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import FormSection from '@/Components/Form/FormSection.vue';
import StickySectionNav from '@/Components/Form/StickySectionNav.vue';
import LanguageTabs from '@/Components/Form/LanguageTabs.vue';
import FormField from '@/Components/Form/FormField.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import SelectInput from '@/Components/Form/SelectInput.vue';
import CheckboxInput from '@/Components/Form/CheckboxInput.vue';
import ToggleSwitch from '@/Components/Form/ToggleSwitch.vue';
import RichTextEditor from '@/Components/Form/RichTextEditor.vue';
import ImageUploader from '@/Components/Form/ImageUploader.vue';
import Repeater from '@/Components/Form/Repeater.vue';
import PitchPositionMarker from '@/Components/Viz/PitchPositionMarker.vue';
import Button from '@/Components/Ui/Button.vue';
import Badge from '@/Components/Ui/Badge.vue';
import Alert from '@/Components/Ui/Alert.vue';
import ConfirmDialog from '@/Components/Ui/ConfirmDialog.vue';

const props = defineProps({
    player: { type: Object, required: true },
    options: { type: Object, required: true },
});

const route = useRoute();
const page = usePage();

const isEdit = computed(() => !!props.player.id);
const locales = computed(() => page.props.locales ?? []);
const defaultLocale = computed(() => page.props.defaultLocale ?? 'ka');

const deleting = ref(false);

const form = useForm(cloneForForm(props.player));

const sections = [
    { id: 'personal', number: '01', label: 'Personal data' },
    { id: 'profile', number: '02', label: 'Player profile' },
    { id: 'career', number: '03', label: 'Career' },
    { id: 'statistics', number: '04', label: 'Statistics' },
    { id: 'photos', number: '05', label: 'Photos' },
    { id: 'goals', number: '06', label: 'Goals' },
    { id: 'meta', number: '07', label: 'Publishing & SEO' },
];

const blankMap = () => Object.fromEntries(locales.value.map((item) => [item.code, '']));
const key = () => Math.random().toString(36).slice(2);

const newSkill = () => ({ _key: key(), id: null, label: blankMap(), value: 50 });
const newCareer = () => ({
    _key: key(),
    id: null,
    club_name: '',
    club_logo_path: null,
    club_logo_url: null,
    started_on: null,
    ended_on: null,
    category: '',
    league: blankMap(),
    notes: blankMap(),
});
const newAchievement = () => ({ _key: key(), id: null, text: blankMap(), year: '' });
const newSeason = () => ({
    _key: key(),
    id: null,
    label: '',
    club_name: '',
    matches_played: 0,
    goals: 0,
    assists: 0,
    minutes_played: 0,
    starting_pct: 0,
    substitute_pct: 0,
    not_in_squad_pct: 0,
    is_current: false,
    months: [],
});
const newMonth = () => ({ _key: key(), id: null, month: 1, goals: 0, assists: 0 });
const newPhoto = () => ({ _key: key(), id: null, path: null, url: null, caption: blankMap() });

function seasonTotal(season) {
    return (
        Number(season.starting_pct || 0) +
        Number(season.substitute_pct || 0) +
        Number(season.not_in_squad_pct || 0)
    );
}

const seasonWarnings = computed(() =>
    form.seasons
        .map((season, index) => ({ index, label: season.label || `Season ${index + 1}`, total: seasonTotal(season) }))
        .filter((item) => item.total !== 0 && item.total !== 100),
);

function addSuggestedSkills() {
    const existing = new Set(form.skills.map((skill) => (skill.label?.en || '').toLowerCase()));

    props.options.suggestedSkills
        .filter((label) => !existing.has(label.toLowerCase()))
        .forEach((label) => {
            const row = newSkill();
            row.label.en = label;
            row.label[defaultLocale.value] = row.label[defaultLocale.value] || label;
            form.skills.push(row);
        });
}

function fillMonths(seasonIndex) {
    const season = form.seasons[seasonIndex];
    const present = new Set(season.months.map((month) => month.month));

    [8, 9, 10, 11, 12, 1, 2, 3, 4, 5].forEach((month) => {
        if (!present.has(month)) {
            season.months.push({ ...newMonth(), month });
        }
    });
}

function markCurrent(index) {
    form.seasons.forEach((season, i) => (season.is_current = i === index));
}

function submit() {
    form.transform((data) => ({
        ...data,
        skills: strip(data.skills),
        career: strip(data.career),
        achievements: strip(data.achievements),
        seasons: strip(data.seasons).map((season) => ({ ...season, months: strip(season.months) })),
        photos: strip(data.photos),
    }));

    isEdit.value
        ? form.put(route('admin.players.update', { player: props.player.id }), { preserveScroll: true, onError: scrollToFirstError })
        : form.post(route('admin.players.store'), { preserveScroll: true, onError: scrollToFirstError });
}

function strip(rows) {
    return (rows ?? []).map(({ _key, ...rest }) => rest);
}

function destroy() {
    form.delete(route('admin.players.destroy', { player: props.player.id }));
}

const monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
</script>

<template>
    <Head :title="isEdit ? 'Edit player' : 'New player'" />

    <AdminLayout
        :title="isEdit ? 'Edit player' : 'New player'"
        :subtitle="isEdit ? `/${player.slug}` : 'Create a new player profile'"
    >
        <template #actions>
            <Button v-if="isEdit" variant="ghost" size="sm" icon="trash" @click="deleting = true">Delete</Button>
            <Button size="sm" icon="save" :loading="form.processing" @click="submit">Save</Button>
        </template>

        <form class="grid gap-6 lg:grid-cols-[13rem_1fr]" @submit.prevent="submit">
            <aside class="hidden lg:block">
                <StickySectionNav :sections="sections" />
            </aside>

            <div class="min-w-0 space-y-6">
                <Alert v-if="Object.keys(form.errors).length" tone="danger" title="Some fields need attention">
                    Check the highlighted language tabs and required fields below.
                </Alert>

                <FormSection id="personal" number="01" title="Personal data">
                    <div class="grid gap-5 sm:grid-cols-[10rem_1fr]">
                        <ImageUploader
                            v-model="form.photo_path"
                            :preview-url="player.photo_url"
                            label="Headshot"
                            :aspect-ratio="1"
                            height="h-40"
                            :error="form.errors.photo_path"
                        />

                        <div class="space-y-4">
                            <LanguageTabs
                                v-model="form.first_name"
                                label="First name"
                                name="first_name"
                                :errors="form.errors"
                                required
                                v-slot="{ locale }"
                            >
                                <TextInput v-model="form.first_name[locale]" placeholder="Maximilian" />
                            </LanguageTabs>

                            <LanguageTabs
                                v-model="form.last_name"
                                label="Last name"
                                name="last_name"
                                :errors="form.errors"
                                required
                                v-slot="{ locale }"
                            >
                                <TextInput v-model="form.last_name[locale]" placeholder="Becker" />
                            </LanguageTabs>
                        </div>
                    </div>

                    <ImageUploader
                        v-model="form.cover_path"
                        :preview-url="player.cover_url"
                        label="Cover / action photo"
                        hint="Used as the full-bleed hero on the public profile."
                        :aspect-ratio="16 / 9"
                        height="h-52"
                        :error="form.errors.cover_path"
                    />

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <FormField label="Date of birth" :error="form.errors.date_of_birth" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.date_of_birth" type="date" :invalid="invalid" />
                        </FormField>

                        <FormField label="Nationality" :error="form.errors.nationality" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.nationality" :invalid="invalid" placeholder="Germany" />
                        </FormField>

                        <FormField label="Preferred foot" :error="form.errors.preferred_foot" v-slot="{ id }">
                            <SelectInput
                                :id="id"
                                v-model="form.preferred_foot"
                                :options="options.feet.map((value) => ({ value, label: value }))"
                                placeholder="Not set"
                            />
                        </FormField>

                        <FormField label="Height" :error="form.errors.height_cm" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.height_cm" type="number" suffix="cm" :invalid="invalid" />
                        </FormField>

                        <FormField label="Weight" :error="form.errors.weight_kg" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.weight_kg" type="number" suffix="kg" :invalid="invalid" />
                        </FormField>

                        <FormField label="Position" :error="form.errors.position" v-slot="{ id }">
                            <SelectInput
                                :id="id"
                                v-model="form.position"
                                :options="options.positions.map((value) => ({ value, label: value }))"
                                placeholder="Not set"
                            />
                        </FormField>
                    </div>

                    <LanguageTabs
                        v-model="form.specific_position"
                        label="Specific position"
                        hint="e.g. Central Midfielder, Left Back."
                        name="specific_position"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <TextInput v-model="form.specific_position[locale]" placeholder="Central Midfielder" />
                    </LanguageTabs>

                    <div class="grid gap-4 sm:grid-cols-[1fr_1fr_8rem]">
                        <FormField label="Current club" :error="form.errors.current_club" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.current_club" :invalid="invalid" />
                        </FormField>

                        <FormField label="Contract until" :error="form.errors.contract_until" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.contract_until" type="date" :invalid="invalid" />
                        </FormField>

                        <ImageUploader
                            v-model="form.current_club_logo_path"
                            :preview-url="player.current_club_logo_url"
                            label="Club logo"
                            height="h-20"
                            :croppable="false"
                        />
                    </div>

                    <fieldset class="rounded-xl border border-border bg-surface-2 p-4">
                        <legend class="px-1.5 text-xs font-semibold uppercase tracking-wide text-fg-subtle">
                            Contact
                        </legend>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <FormField label="Phone" :error="form.errors.phone" v-slot="{ id, invalid }">
                                <TextInput :id="id" v-model="form.phone" icon="phone" :invalid="invalid" />
                            </FormField>
                            <FormField label="Email" :error="form.errors.email" v-slot="{ id, invalid }">
                                <TextInput :id="id" v-model="form.email" type="email" icon="mail" :invalid="invalid" />
                            </FormField>
                            <FormField label="Instagram" :error="form.errors.instagram" v-slot="{ id, invalid }">
                                <TextInput :id="id" v-model="form.instagram" icon="instagram" :invalid="invalid" placeholder="@handle" />
                            </FormField>
                            <FormField label="City" :error="form.errors.city" v-slot="{ id, invalid }">
                                <TextInput :id="id" v-model="form.city" icon="location" :invalid="invalid" />
                            </FormField>
                            <FormField label="Country" :error="form.errors.country" v-slot="{ id, invalid }">
                                <TextInput :id="id" v-model="form.country" icon="globe" :invalid="invalid" />
                            </FormField>
                        </div>
                    </fieldset>
                </FormSection>

                <FormSection id="profile" number="02" title="Player profile">
                    <LanguageTabs
                        v-model="form.playing_style"
                        label="Playing style"
                        name="playing_style"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <RichTextEditor v-model="form.playing_style[locale]" placeholder="Describe the player's strengths and style…" />
                    </LanguageTabs>

                    <div class="grid gap-8 lg:grid-cols-[1fr_16rem]">
                        <Repeater
                            v-model="form.skills"
                            label="Strengths / skill ratings"
                            hint="Fully editable — rename, reorder or remove any of these."
                            add-label="Add strength"
                            empty-label="No strengths added yet."
                            :new-row="newSkill"
                            :max="24"
                        >
                            <template #summary="{ row }">
                                <span class="truncate text-sm text-fg-muted">
                                    {{ row.label?.[defaultLocale] || row.label?.en || 'Untitled' }}
                                </span>
                            </template>

                            <template #default="{ row, index }">
                                <div class="grid gap-4 sm:grid-cols-[1fr_12rem]">
                                    <LanguageTabs
                                        v-model="row.label"
                                        label="Label"
                                        :name="`skills.${index}.label`"
                                        :errors="form.errors"
                                        :copyable="false"
                                        v-slot="{ locale }"
                                    >
                                        <TextInput v-model="row.label[locale]" size="sm" placeholder="Game Intelligence" />
                                    </LanguageTabs>

                                    <FormField :label="`Rating — ${row.value}`" v-slot="{ id }">
                                        <input
                                            :id="id"
                                            v-model.number="row.value"
                                            type="range"
                                            min="0"
                                            max="100"
                                            class="h-2 w-full cursor-pointer appearance-none rounded-full bg-surface-3 accent-[var(--accent)]"
                                        >
                                    </FormField>
                                </div>
                            </template>
                        </Repeater>

                        <div>
                            <p class="mb-2 text-sm font-medium text-fg">Position on the pitch</p>
                            <PitchPositionMarker
                                :x="form.pitch_x"
                                :y="form.pitch_y"
                                editable
                                @update:x="form.pitch_x = $event"
                                @update:y="form.pitch_y = $event"
                            />
                        </div>
                    </div>

                    <Button variant="outline" size="sm" icon="plus" @click="addSuggestedSkills">
                        Add suggested strengths
                    </Button>
                </FormSection>

                <FormSection id="career" number="03" title="Career">
                    <Repeater
                        v-model="form.career"
                        label="Career timeline"
                        add-label="Add club"
                        empty-label="No career entries yet."
                        :new-row="newCareer"
                        collapsible
                        :max="40"
                    >
                        <template #summary="{ row }">
                            <span class="truncate text-sm text-fg-muted">{{ row.club_name || 'New entry' }}</span>
                        </template>

                        <template #default="{ row, index }">
                            <div class="space-y-4">
                                <div class="grid gap-4 sm:grid-cols-[7rem_1fr_1fr]">
                                    <ImageUploader
                                        v-model="row.club_logo_path"
                                        :preview-url="row.club_logo_url"
                                        label="Logo"
                                        height="h-20"
                                        :croppable="false"
                                    />

                                    <FormField
                                        label="Club"
                                        :error="form.errors[`career.${index}.club_name`]"
                                        required
                                        v-slot="{ id, invalid }"
                                    >
                                        <TextInput :id="id" v-model="row.club_name" size="sm" :invalid="invalid" />
                                    </FormField>

                                    <FormField label="Category" hint="U17 / U19 / Senior…" v-slot="{ id }">
                                        <TextInput :id="id" v-model="row.category" size="sm" placeholder="Senior" />
                                    </FormField>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <FormField label="From" v-slot="{ id }">
                                        <TextInput :id="id" v-model="row.started_on" type="date" size="sm" />
                                    </FormField>
                                    <FormField
                                        label="To"
                                        hint="Leave empty if still at the club."
                                        :error="form.errors[`career.${index}.ended_on`]"
                                        v-slot="{ id, invalid }"
                                    >
                                        <TextInput :id="id" v-model="row.ended_on" type="date" size="sm" :invalid="invalid" />
                                    </FormField>
                                </div>

                                <LanguageTabs
                                    v-model="row.league"
                                    label="League / division"
                                    :name="`career.${index}.league`"
                                    :errors="form.errors"
                                    :copyable="false"
                                    v-slot="{ locale }"
                                >
                                    <TextInput v-model="row.league[locale]" size="sm" placeholder="Regionalliga West" />
                                </LanguageTabs>

                                <LanguageTabs
                                    v-model="row.notes"
                                    label="Notes"
                                    :name="`career.${index}.notes`"
                                    :errors="form.errors"
                                    :copyable="false"
                                    v-slot="{ locale }"
                                >
                                    <TextInput v-model="row.notes[locale]" size="sm" />
                                </LanguageTabs>
                            </div>
                        </template>
                    </Repeater>

                    <hr class="border-border">

                    <Repeater
                        v-model="form.achievements"
                        label="Achievements"
                        add-label="Add achievement"
                        empty-label="No achievements yet."
                        :new-row="newAchievement"
                        :max="60"
                    >
                        <template #summary="{ row }">
                            <span class="truncate text-sm text-fg-muted">
                                {{ row.text?.[defaultLocale] || 'New achievement' }}
                            </span>
                        </template>

                        <template #default="{ row, index }">
                            <div class="grid gap-4 sm:grid-cols-[1fr_7rem]">
                                <LanguageTabs
                                    v-model="row.text"
                                    label="Achievement"
                                    :name="`achievements.${index}.text`"
                                    :errors="form.errors"
                                    :copyable="false"
                                    v-slot="{ locale }"
                                >
                                    <TextInput v-model="row.text[locale]" size="sm" placeholder="Promotion to Regionalliga" />
                                </LanguageTabs>

                                <FormField label="Year" v-slot="{ id }">
                                    <TextInput :id="id" v-model="row.year" size="sm" placeholder="2024" />
                                </FormField>
                            </div>
                        </template>
                    </Repeater>
                </FormSection>

                <FormSection id="statistics" number="04" title="Season statistics">
                    <Alert v-if="seasonWarnings.length" tone="warning" title="Playing-time split does not total 100%">
                        <ul class="mt-1 space-y-0.5">
                            <li v-for="warning in seasonWarnings" :key="warning.index">
                                {{ warning.label }} — {{ warning.total }}%
                            </li>
                        </ul>
                        This is a warning only; you can still save.
                    </Alert>

                    <Repeater
                        v-model="form.seasons"
                        label="Seasons"
                        add-label="Add season"
                        empty-label="No seasons recorded yet."
                        :new-row="newSeason"
                        collapsible
                        :max="30"
                    >
                        <template #summary="{ row }">
                            <span class="flex items-center gap-2 truncate text-sm text-fg-muted">
                                {{ row.label || 'New season' }}
                                <Badge v-if="row.is_current" tone="accent" size="sm">Current</Badge>
                            </span>
                        </template>

                        <template #default="{ row, index }">
                            <div class="space-y-5">
                                <div class="grid gap-4 sm:grid-cols-[10rem_1fr_auto]">
                                    <FormField
                                        label="Season"
                                        :error="form.errors[`seasons.${index}.label`]"
                                        required
                                        v-slot="{ id, invalid }"
                                    >
                                        <TextInput :id="id" v-model="row.label" size="sm" placeholder="2024/2025" :invalid="invalid" />
                                    </FormField>

                                    <FormField label="Club" v-slot="{ id }">
                                        <TextInput :id="id" v-model="row.club_name" size="sm" />
                                    </FormField>

                                    <div class="flex items-end pb-1">
                                        <CheckboxInput
                                            :model-value="row.is_current"
                                            label="Current season"
                                            @update:model-value="markCurrent(index)"
                                        />
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-4">
                                    <FormField label="Matches" v-slot="{ id }">
                                        <TextInput :id="id" v-model="row.matches_played" type="number" size="sm" min="0" />
                                    </FormField>
                                    <FormField label="Goals" v-slot="{ id }">
                                        <TextInput :id="id" v-model="row.goals" type="number" size="sm" min="0" />
                                    </FormField>
                                    <FormField label="Assists" v-slot="{ id }">
                                        <TextInput :id="id" v-model="row.assists" type="number" size="sm" min="0" />
                                    </FormField>
                                    <FormField label="Minutes" v-slot="{ id }">
                                        <TextInput :id="id" v-model="row.minutes_played" type="number" size="sm" min="0" />
                                    </FormField>
                                </div>

                                <fieldset class="rounded-xl border border-border bg-surface p-4">
                                    <legend class="flex items-center gap-2 px-1.5 text-xs font-semibold uppercase tracking-wide text-fg-subtle">
                                        Playing time
                                        <span
                                            :class="[
                                                'tabular-nums',
                                                seasonTotal(row) === 100 ? 'text-success' : seasonTotal(row) === 0 ? '' : 'text-warning',
                                            ]"
                                        >{{ seasonTotal(row) }}%</span>
                                    </legend>

                                    <div class="grid gap-4 sm:grid-cols-3">
                                        <FormField label="Starting XI" v-slot="{ id }">
                                            <TextInput :id="id" v-model="row.starting_pct" type="number" size="sm" min="0" max="100" suffix="%" />
                                        </FormField>
                                        <FormField label="Substitute" v-slot="{ id }">
                                            <TextInput :id="id" v-model="row.substitute_pct" type="number" size="sm" min="0" max="100" suffix="%" />
                                        </FormField>
                                        <FormField label="Not in squad" v-slot="{ id }">
                                            <TextInput :id="id" v-model="row.not_in_squad_pct" type="number" size="sm" min="0" max="100" suffix="%" />
                                        </FormField>
                                    </div>
                                </fieldset>

                                <div>
                                    <div class="mb-2 flex items-center justify-between gap-3">
                                        <p class="text-sm font-medium text-fg">Goals &amp; assists by month</p>
                                        <Button variant="ghost" size="xs" icon="plus" @click="fillMonths(index)">
                                            Fill season months
                                        </Button>
                                    </div>

                                    <Repeater
                                        v-model="row.months"
                                        add-label="Add month"
                                        empty-label="No monthly data."
                                        :new-row="newMonth"
                                        :boxed="false"
                                        :max="12"
                                    >
                                        <template #default="{ row: month }">
                                            <div class="grid gap-3 sm:grid-cols-3">
                                                <FormField label="Month" v-slot="{ id }">
                                                    <SelectInput
                                                        :id="id"
                                                        v-model="month.month"
                                                        size="sm"
                                                        :options="monthNames.slice(1).map((label, i) => ({ value: i + 1, label }))"
                                                    />
                                                </FormField>
                                                <FormField label="Goals" v-slot="{ id }">
                                                    <TextInput :id="id" v-model="month.goals" type="number" size="sm" min="0" />
                                                </FormField>
                                                <FormField label="Assists" v-slot="{ id }">
                                                    <TextInput :id="id" v-model="month.assists" type="number" size="sm" min="0" />
                                                </FormField>
                                            </div>
                                        </template>
                                    </Repeater>
                                </div>
                            </div>
                        </template>
                    </Repeater>
                </FormSection>

                <FormSection id="photos" number="05" title="Match photos">
                    <Repeater
                        v-model="form.photos"
                        add-label="Add photo"
                        empty-label="No photos yet."
                        :new-row="newPhoto"
                        :max="60"
                    >
                        <template #summary="{ row }">
                            <span class="truncate text-sm text-fg-muted">
                                {{ row.caption?.[defaultLocale] || 'Photo' }}
                            </span>
                        </template>

                        <template #default="{ row, index }">
                            <div class="grid gap-4 sm:grid-cols-[12rem_1fr]">
                                <ImageUploader
                                    v-model="row.path"
                                    :preview-url="row.url"
                                    :aspect-ratio="4 / 3"
                                    height="h-32"
                                    :error="form.errors[`photos.${index}.path`]"
                                />

                                <LanguageTabs
                                    v-model="row.caption"
                                    label="Caption"
                                    :name="`photos.${index}.caption`"
                                    :errors="form.errors"
                                    :copyable="false"
                                    v-slot="{ locale }"
                                >
                                    <TextInput v-model="row.caption[locale]" size="sm" />
                                </LanguageTabs>
                            </div>
                        </template>
                    </Repeater>
                </FormSection>

                <FormSection id="goals" number="06" title="Goals">
                    <LanguageTabs
                        v-model="form.goals_short_term"
                        label="Short-term goals"
                        name="goals_short_term"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <RichTextEditor v-model="form.goals_short_term[locale]" compact min-height="7rem" />
                    </LanguageTabs>

                    <LanguageTabs
                        v-model="form.goals_mid_term"
                        label="Mid-term goals"
                        name="goals_mid_term"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <RichTextEditor v-model="form.goals_mid_term[locale]" compact min-height="7rem" />
                    </LanguageTabs>

                    <LanguageTabs
                        v-model="form.goals_long_term"
                        label="Long-term goals"
                        name="goals_long_term"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <RichTextEditor v-model="form.goals_long_term[locale]" compact min-height="7rem" />
                    </LanguageTabs>

                    <LanguageTabs
                        v-model="form.quote"
                        label="Personal quote"
                        hint="Shown as a pull-quote at the end of the public profile."
                        name="quote"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <TextInput v-model="form.quote[locale]" />
                    </LanguageTabs>
                </FormSection>

                <FormSection id="meta" number="07" title="Publishing &amp; SEO">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <FormField label="Status" :error="form.errors.status" v-slot="{ id }">
                            <SelectInput
                                :id="id"
                                v-model="form.status"
                                :options="options.statuses.map((value) => ({ value, label: value }))"
                            />
                        </FormField>

                        <FormField label="Slug" hint="Auto-generated if left empty." :error="form.errors.slug" v-slot="{ id, invalid }">
                            <TextInput :id="id" v-model="form.slug" :invalid="invalid" placeholder="maximilian-becker" />
                        </FormField>

                        <FormField label="Sort order" :error="form.errors.sort_order" v-slot="{ id }">
                            <TextInput :id="id" v-model="form.sort_order" type="number" min="0" />
                        </FormField>
                    </div>

                    <ToggleSwitch
                        v-model="form.is_featured"
                        label="Feature this player"
                        hint="Featured players are highlighted in listings."
                    />

                    <LanguageTabs v-model="form.seo_title" label="SEO title" name="seo_title" :errors="form.errors" v-slot="{ locale }">
                        <TextInput v-model="form.seo_title[locale]" />
                    </LanguageTabs>

                    <LanguageTabs
                        v-model="form.seo_description"
                        label="SEO description"
                        name="seo_description"
                        :errors="form.errors"
                        v-slot="{ locale }"
                    >
                        <TextInput v-model="form.seo_description[locale]" />
                    </LanguageTabs>
                </FormSection>

                <div class="flex flex-wrap items-center justify-end gap-3 pb-6">
                    <Button variant="ghost" :href="route('admin.players.index')">Cancel</Button>
                    <Button type="submit" icon="save" :loading="form.processing">
                        {{ isEdit ? 'Save changes' : 'Create player' }}
                    </Button>
                </div>
            </div>
        </form>

        <ConfirmDialog
            :open="deleting"
            title="Delete player?"
            message="This removes the player and all their seasons, photos and career entries from the public site."
            @cancel="deleting = false"
            @confirm="destroy"
        />
    </AdminLayout>
</template>
