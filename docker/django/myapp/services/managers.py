from random import choice
from .repositories import DoctorRepository
from .models import Doctor

class DoctorManager:
    def __init__(self, doctors_in_schedule):
        self.doctors_in_schedule = doctors_in_schedule

    def is_day_off(self, doctor):
        # Ваш метод для проверки выходного дня
        pass

    def get_random_doctor_who_can(self, competency, doctors_already_in_day):
        doctors = []

        if self.doctors_in_schedule:
            doctors_in = DoctorRepository.find_by_ids(
                ids=self.doctors_in_schedule,
                exclude=doctors_already_in_day,
                modality=competency.get_modality()
            )

            doctors = [doctor for doctor in doctors_in if not self.is_day_off(doctor)]

        if not doctors:
            doctors = DoctorRepository.find_by_ids(
                exclude=doctors_already_in_day,
                modality=competency.get_modality()
            )

            doctors = [doctor for doctor in doctors if not self.is_day_off(doctor)]

        if not doctors:
            doctors_in = DoctorRepository.find_by_ids(
                ids=self.doctors_in_schedule,
                exclude=doctors_already_in_day,
                addon_modality=competency.get_modality()
            )

            doctors = [doctor for doctor in doctors_in if not self.is_day_off(doctor)]

        if not doctors:
            doctors = DoctorRepository.find_by_ids(
                exclude=doctors_already_in_day,
                addon_modality=competency.get_modality()
            )

            doctors = [doctor for doctor in doctors if not self.is_day_off(doctor)]

        if doctors:
            return choice(doctors)
        else:
            return None