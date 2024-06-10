import random
import math
import sys
from datetime import datetime, timedelta
from django.db import transaction
from django.utils import timezone
from django.db.models import Q
# Импорт моделей Django
from ..models import Competencies, WeekStudies, TempSchedule, TempScheduleWeekStudies, TempDoctorSchedule, Doctor

class AlgorithmWeekService:
    EVOLUTION_COUNT = 20
    POPULATION_COUNT = 1

    def __init__(self, start_date, end_date):
        self.doctors_stat = {}
        self.modalities = list(Competencies.objects.all())
        self.doctors_in_schedule = []
        self.current_day = None
        self.weeks_number = list(WeekStudies.objects.filter(start_of_week__range=(start_date, end_date)).values_list('week_number', flat=True).distinct())

    def run(self):
        population = self.initialize_population()

        #for _ in range(self.EVOLUTION_COUNT):
        #    if len(population) > 1:
        #        population = self.evolve_population(population)

    def initialize_population(self):
        population = []
        for _ in range(self.POPULATION_COUNT):
            random_schedule = self.create_random_schedule()
            population.append(random_schedule)
            self.save_temp_schedule(random_schedule)
        return population

    @transaction.atomic
    def save_temp_schedule(self, random_schedule):
        try:
            temp_schedule_entity = TempSchedule.objects.create(created_at=timezone.now())
            temp_schedule_entity.save()
            for week_number, schedule_for_modality in random_schedule.items():
                for modality, schedule_week in schedule_for_modality.items():
                    competency = Competencies.objects.get(modality=modality)
                    week_studies = WeekStudies.objects.get(week_number=week_number, competency=competency)
                    temp_schedule_week_studies = TempScheduleWeekStudies.objects.create(
                        temp_schedule=temp_schedule_entity,
                        week_studies=week_studies,
                        empty=0
                    )
                    empty = 0
                    temp_schedule_week_studies.save()
                    for day, schedule_day in schedule_week.items():
                        for doctor_id, stat in schedule_day.items():
                            if doctor_id == 'empty':
                                empty = stat
                                continue

                            doctor = Doctor.objects.get(id=doctor_id)
                            temp_doctor_schedule = TempDoctorSchedule.objects.create(
                                doctor=doctor,
                                date=datetime.strptime(day, '%Y-%m-%d'),
                                temp_schedule=temp_schedule_week_studies
                            )
                            temp_doctor_schedule.save()
                    temp_schedule_week_studies.empty = empty
                    temp_schedule_week_studies.save()
        except Exception as e:
            logger.error(f'Error saving temp schedule: {e}')
            raise

    def create_random_schedule(self):
        schedule = {}
        self.doctors_stat = {}
        self.doctors_in_schedule = []
        for week_number in self.weeks_number:
            week_studies = list(WeekStudies.objects.filter(week_number=week_number))
            random.shuffle(week_studies)
            for modality_week in week_studies:
                modality_competency = modality_week.competency
                modality_day_count = modality_week.count // 7
                cycle_per_day_count = math.ceil(modality_day_count / modality_competency.minimal_count_per_shift)
                ost_per_day_count = 0
                for day in range(0, 6):
                    current_date = modality_week.start_of_week + timedelta(days=day)
                    current_date_str = current_date.strftime('%Y-%m-%d')
                    self.current_day = current_date_str
                    doctors_in_day = []
                    schedule.setdefault(week_number, {}).setdefault(modality_competency.modality, {}).setdefault(current_date_str, {'empty': 0})
                    ost_per_day_count += modality_day_count
                    for _ in range(cycle_per_day_count):
                        schedule[week_number][modality_competency.modality][current_date_str]['empty'] = ost_per_day_count
                        doctor = self.get_random_doctor_who_can(modality_competency, doctors_in_day)
                        if doctor:
                            modality_doctor_minimal_count_per_shift = round(modality_competency.minimal_count_per_shift * doctor.stavka)
                            modality_doctor_max_count_per_shift = modality_competency.max_count_per_shift * doctor.stavka

                        if not doctor:
                            break
                        elif ost_per_day_count >= modality_doctor_minimal_count_per_shift and ost_per_day_count <= modality_doctor_max_count_per_shift:
                            schedule[week_number][modality_competency.modality][current_date_str][doctor.id] = {
                                'get': ost_per_day_count,
                                'doMax': int(modality_doctor_max_count_per_shift - ost_per_day_count)
                            }
                            doctors_in_day.append(doctor.id)
                            self.doctors_stat[doctor.id] = {
                                'coefficient': ost_per_day_count * modality_competency.coefficient,
                                'shiftCount': 1
                            }
                            ost_per_day_count = 0
                            schedule[week_number][modality_competency.modality][current_date_str]['empty'] = ost_per_day_count
                            self.doctors_in_schedule.append(doctor.id)
                            break
                        elif ost_per_day_count >= modality_doctor_minimal_count_per_shift:
                            count_per_shift = random.randint(
                                round(modality_doctor_minimal_count_per_shift),
                                int(modality_doctor_max_count_per_shift)
                            )
                            schedule[week_number][modality_competency.modality][current_date_str][doctor.id] = {
                                'get': count_per_shift,
                                'doMax': int(modality_doctor_max_count_per_shift - count_per_shift)
                            }
                            ost_per_day_count -= count_per_shift
                            doctors_in_day.append(doctor.id)
                            self.doctors_in_schedule.append(doctor.id)
                            self.doctors_stat[doctor.id] = {
                                'coefficient': count_per_shift * modality_competency.coefficient,
                                'shiftCount': 1
                            }
                        else:
                            ost_per_day_count = self.set_on_active_doctors(schedule, ost_per_day_count, current_date_str, modality_competency.modality, week_number)
                            if ost_per_day_count > 0:
                                schedule[week_number][modality_competency.modality][current_date_str][doctor.id] = {
                                    'get': ost_per_day_count,
                                    'doMax': int(modality_doctor_max_count_per_shift - ost_per_day_count)
                                }
                                doctors_in_day.append(doctor.id)
                                self.doctors_in_schedule.append(doctor.id)
                                self.doctors_stat[doctor.id] = {
                                    'coefficient': ost_per_day_count * modality_competency.coefficient,
                                    'shiftCount': 1
                                }
                                ost_per_day_count = 0
                            schedule[week_number][modality_competency.modality][current_date_str]['empty'] = ost_per_day_count
                            break
        print(schedule)
        sys.exit(1)
        return schedule

    def set_on_active_doctors(self, schedule, ost, current_date_str, modality, week_number):
        for doctor_id, doctor_day_schedule in schedule[week_number][modality][current_date_str].items():
            if doctor_id == 'empty':
                continue
            if not doctor_day_schedule['doMax']:
                continue
            if doctor_day_schedule['doMax'] >= ost:
                doctor_day_schedule['get'] += ost
                doctor_day_schedule['doMax'] -= ost
                ost = 0
                break
            else:
                ost -= doctor_day_schedule['doMax']
                doctor_day_schedule['get'] += doctor_day_schedule['doMax']
                doctor_day_schedule['doMax'] = 0
        return ost

    def is_day_off(self, doctor):
        try
            doctor_work_schedule = doctor.work_schedule
            if not doctor_work_schedule:
                return True
            if doctor.id not in self.doctors_stat:
                self.doctors_stat[doctor.id] = {
                    'offCount': 0,
                    'shiftCount': 0,
                    'lastOffDay': None
                }
                self.doctors_stat[doctor.id].setdefault('offCount', 0)
                self.doctors_stat[doctor.id].setdefault('shiftCount', 0)
                self.doctors_stat[doctor.id].setdefault('lastOffDay', None)
                return False
            #print(self.doctors_stat)
            #sys.exit(1)
            if self.doctors_stat[doctor.id]['offCount'] >= doctor_work_schedule.days_off and self.doctors_stat[doctor.id]['lastOffDay'] != self.current_day:
                self.doctors_stat[doctor.id]['offCount'] = 0
                self.doctors_stat[doctor.id]['shiftCount'] = 0
                return False
            if self.doctors_stat[doctor.id]['shiftCount'] >= doctor_work_schedule.shift_per_cycle:
                if self.doctors_stat[doctor.id]['lastOffDay'] != self.current_day:
                    self.doctors_stat[doctor.id]['lastOffDay'] = self.current_day
                    self.doctors_stat[doctor.id]['offCount'] += 1
                    return True
            return False
        except Exception as e:
            print(f'{e}')
            sys.exit(1)
            logger.error(f'{e}')
                                                                                                                raise

    def get_random_doctor_who_can(self, modality, doctors_in_day):
                doctors = []
                if self.doctors_in_schedule:
                    doctors_in = Doctor.objects.filter(
                        id__in=self.doctors_in_schedule,
                        main_competencies__contains=[modality.modality]
                    ).exclude(
                         id__in=doctors_in_day
                    )

                    doctors = [doctor for doctor in doctors_in if not self.is_day_off(doctor)]

                if not doctors:
                    doctors = Doctor.objects.filter(
                        main_competencies__contains=[modality.modality]
                    ).exclude(id__in=doctors_in_day)

                    doctors = [doctor for doctor in doctors if not self.is_day_off(doctor)]

                if not doctors:
                    doctors_in = Doctor.objects.filter(
                        id__in=self.doctors_in_schedule,
                        addon_competencies__contains=[modality.modality]
                    ).exclude(id__in=doctors_in_day)

                    doctors = [doctor for doctor in doctors_in if not self.is_day_off(doctor)]

                if not doctors:
                    doctors = Doctor.objects.filter(
                        ~Q(id__in=doctors_in_day),
                        addon_competencies__contains=[modality.modality]
                    ).exclude(id__in=doctors_in_day)

                    doctors = [doctor for doctor in doctors if not self.is_day_off(doctor)]

                if doctors:
                    return random.choice(doctors)
                else:
                    return None

    def evolve_population(self, population):
        min_empty = float('inf')
        schedule_with_min_empty = None
        min_index = 0
        for i, schedule in enumerate(population):
            total_empty = sum(
                schedule[week_number][modality][day]['empty']
                for week_number in self.weeks_number
                for modality in schedule[week_number]
                for day in schedule[week_number][modality]
            )
            if total_empty < min_empty:
                min_empty = total_empty
                schedule_with_min_empty = schedule
                min_index = i
        for i in range(len(population)):
            if i != min_index:
                population[i] = self.mutate(schedule_with_min_empty)
        return population

    def mutate(self, schedule):
        new_schedule = schedule.copy()
        doctor_id = random.choice(list(self.doctors_stat.keys()))
        weeks_number = list(new_schedule.keys())
        week = random.choice(weeks_number)
        modalities = list(new_schedule[week].keys())
        modality = random.choice(modalities)
        days = list(new_schedule[week][modality].keys())
        day = random.choice(days)
        new_schedule[week][modality][day].pop(doctor_id, None)
        self.doctors_in_schedule = [d for d in self.doctors_in_schedule if d != doctor_id]
        self.current_day = day
        ost = new_schedule[week][modality][day]['empty']
        ost = self.set_on_active_doctors(new_schedule, ost, day, modality, week)
        new_schedule[week][modality][day]['empty'] = ost

        doctor = self.get_random_doctor_who_can(Competencies.objects.get(modality=modality), [])
        if doctor:
            new_schedule[week][modality][day][doctor.id] = {
                'get': ost,
                'doMax': int(Competencies.objects.get(modality=modality).max_count_per_shift * doctor.stavka - ost)
            }
            self.doctors_in_schedule.append(doctor.id)
            self.doctors_stat[doctor.id] = {
                'coefficient': ost * Competencies.objects.get(modality=modality).coefficient,
                'shiftCount': 1
            }
        return new_schedule