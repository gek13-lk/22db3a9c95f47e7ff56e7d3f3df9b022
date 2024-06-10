from django.core.management.base import BaseCommand

from kombu import Connection, Exchange, Queue, Consumer, Producer
import random
from datetime import datetime, timedelta
from django.db import transaction
from myapp.services.schedule_service import ScheduleService
# Импорт моделей Django
from ...models import Role

class Command(BaseCommand):
    help = 'Generate schedule using AlgorithmWeekService'

    def handle(self, *args, **kwargs):
        current_datetime = datetime.now()
        formatted_datetime = current_datetime.strftime("%Y-%m-%d %H:%M:%S")
        start_date = '2024-01-01'
        end_date = '2024-01-08'
        self.stdout.write(self.style.SUCCESS(formatted_datetime))

        schedule_service = ScheduleService(start_date, end_date)
        schedule_service.generate_schedule()

        current_datetime = datetime.now()
        formatted_datetime = current_datetime.strftime("%Y-%m-%d %H:%M:%S")
        self.stdout.write(self.style.SUCCESS(formatted_datetime))
        self.stdout.write(self.style.SUCCESS('Successfully generated schedule'))
