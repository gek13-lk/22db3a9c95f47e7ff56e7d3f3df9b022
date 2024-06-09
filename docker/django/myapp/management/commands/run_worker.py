from django.core.management.base import BaseCommand

from kombu import Connection, Exchange, Queue, Consumer, Producer

# Импорт моделей Django
from ...models import Role

class Command(BaseCommand):
    help = 'Запуск обработчика очереди'

    def handle(self, *args, **options):
        # Обменник и очереди
        exchange = Exchange('default', type='direct')
        from_task_queue = Queue('from_task', exchange, routing_key='from_task')
        to_task_queue = Queue('to_task', exchange, routing_key='to_task')

        def handle_message(body, message):
            print(f"Received message: {body}")

            # Обработка сообщения
            result = body  # Пример: возвращаем тело сообщения без изменений

            try:
                # Публикация результата в другую очередь
                with Connection('amqp://guest:guest@rabbitmq:5672//') as conn:
                    # Пример взаимодействия с базой данных
                    role = Role.objects.get(id=result)
                    print(f"Get role: code={role.code}, name={role.name}")

                    producer = Producer(conn, serializer='json')
                    producer.publish(result, exchange=exchange, routing_key='to_task')
                    print(f"Sent result: {result}")
                    message.ack()

            except Exception as e:
                print(f"Failed to send message: {e}")
                message.reject()

        def run_worker():
            with Connection('amqp://guest:guest@rabbitmq:5672//') as conn:
                # Явное создание очередей
                from_task_queue(conn).declare()
                to_task_queue(conn).declare()

                with Consumer(conn, from_task_queue, callbacks=[handle_message], accept=['json']) as consumer:
                    print("Worker is running...")

                    while True:
                        conn.drain_events()

        run_worker()

