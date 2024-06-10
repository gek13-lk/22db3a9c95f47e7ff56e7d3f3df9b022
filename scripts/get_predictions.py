from flask import Flask, request, jsonify
import os
import sys
import json
import pandas as pd
from joblib import load
import script_date

app = Flask(__name__)

model_dir = '../storage/'

model_paths = [
    'count_ct_model.pkl', 'count_mri_model.pkl', 'count_rg_model.pkl', 'count_flg_model.pkl', 'count_mmg_model.pkl', 'count_densitometer_model.pkl',
    'count_ct_with_ku_1_zone_model.pkl', 'count_mri_with_ku_1_zone_model.pkl', 'count_ct_with_ku_more_than_1_zone_model.pkl', 'count_mri_with_ku_more_than_1_zone_model.pkl'
]

models = [load(os.path.join(model_dir, model_path)) for model_path in model_paths]

def predict_weekly(year, month):
    weeks = script_date.get_weeks_in_month(year, month)
    predictions = []

    for week in weeks:
        month = script_date.month_from_week(week['Year'], week['Week'])
        week_features = pd.DataFrame([{
            'Year': week['Year'],
            'Week': week['Week'],
            'Month': month
        }])

        week_prediction = {model_name: int(model.predict(week_features)[0])
                           for model, model_name in zip(models, ['ct', 'mri', 'rg', 'flg', 'mmg', 'densitometer', 'ct_with_ku_1_zone', 'mri_with_ku_1_zone', 'ct_with_ku_more_than_1_zone', 'mri_with_ku_more_than_1_zone'])}

        week_prediction.update({'Week': week['Week'], 'Year': week['Year']})
        predictions.append(week_prediction)

    return predictions

@app.route('/python/predicted_studies', methods=['POST'])
def predict_weekly_endpoint():
    data = request.json
    year = int(data.get('year'))
    month = int(data.get('month'))
    predictions = predict_weekly(year, month)
    return jsonify({'predictions': predictions}), 200

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8000)
