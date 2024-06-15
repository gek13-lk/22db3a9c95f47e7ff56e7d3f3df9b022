from django.http import JsonResponse
from django.views.decorators.csrf import csrf_exempt
import json
from asgiref.sync import async_to_sync
from schedule_project.model_training import train_models_async
from schedule_project.get_predictions import get_prediction_data

@csrf_exempt
def run_script_train_model(request):
    if request.method == 'POST':
        try:
            data = json.loads(request.body).get('data')
            if data:
                async_to_sync(train_models_async)(data)
                return JsonResponse({'status': 'success'})
            else:
                return JsonResponse({'status': 'error', 'error': 'No data provided'}, status=400)
        except json.JSONDecodeError:
            return JsonResponse({'status': 'error', 'error': 'Invalid JSON'}, status=400)
    return JsonResponse({'status': 'error', 'error': 'Invalid method'}, status=405)

@csrf_exempt
def run_script_get_prediction_data(request):
    if request.method == 'POST':
        try:
            data = json.loads(request.body)
            year = int(data.get('year'))
            month = int(data.get('month'))

            if year is not None and month is not None:
                result = get_prediction_data(year, month)
                return JsonResponse({'status': 'success', 'result': result}, status=200)
            else:
                return JsonResponse({'status': 'error', 'error': 'No data provided'}, status=400)
        except json.JSONDecodeError:
            return JsonResponse({'status': 'error', 'error': 'Invalid JSON'}, status=400)
    return JsonResponse({'status': 'error', 'error': 'Invalid method'}, status=405)
