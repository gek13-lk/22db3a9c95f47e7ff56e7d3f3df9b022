from datetime import datetime
from .algorithm_week_service import AlgorithmWeekService  # Предполагая, что этот класс в файле algorithm_week_service.py

class ScheduleService:
    def __init__(self, start_date_str, end_date_str):
        self.start_date = datetime.strptime(start_date_str, '%Y-%m-%d')
        self.end_date = datetime.strptime(end_date_str, '%Y-%m-%d')

    def generate_schedule(self):
        algorithm_service = AlgorithmWeekService(self.start_date, self.end_date)
        algorithm_service.run()
