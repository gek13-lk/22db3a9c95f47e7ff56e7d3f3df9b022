from django.core.management.base import BaseCommand

from kombu import Connection, Exchange, Queue, Consumer, Producer

# Импорт моделей Django
from ...models import Role

class Command(BaseCommand):
    help = 'Запуск обработчика очереди'

    def handle(self, *args, **options):
        # Обменник и очереди


        def run_worker():
            print('sds')
        run_worker()

