import os
import sys
import json
import pandas as pd
from joblib import load
from schedule_project.script_date import month_from_week, season_from_month, get_weeks_in_month

model_paths = [
    'count_ct_model.pkl', 'count_mri_model.pkl', 'count_rg_model.pkl', 'count_flg_model.pkl', 'count_mmg_model.pkl', 'count_densitometer_model.pkl',
    'count_ct_with_ku_1_zone_model.pkl', 'count_mri_with_ku_1_zone_model.pkl', 'count_ct_with_ku_more_than_1_zone_model.pkl', 'count_mri_with_ku_more_than_1_zone_model.pkl'
]

def get_prediction_data(year, month):
    models = [load(f"./storage/{model_path}") for model_path in model_paths]

    weeks = get_weeks_in_month(year, month)
    predictions = []

    for week in weeks:
        month = month_from_week(week['Year'], week['Week'])

        week_features = pd.DataFrame([{
            'Year': week['Year'],
            'Week': week['Week'],
            'Month': month,
            'Season_Autumn': 1 if season_from_month(month) == 'Autumn' else 0,
            'Season_Spring': 1 if season_from_month(month) == 'Spring' else 0,
            'Season_Summer': 1 if season_from_month(month) == 'Summer' else 0,
            'Season_Winter': 1 if season_from_month(month) == 'Winter' else 0
        }])

        week_prediction = {model_name: int(model.predict(week_features)[0])
                           for model, model_name in zip(models, ['ct', 'mri', 'rg', 'flg', 'mmg', 'densitometer', 'ct_with_ku_1_zone', 'mri_with_ku_1_zone', 'ct_with_ku_more_than_1_zone', 'mri_with_ku_more_than_1_zone'])}

        week_prediction.update({
            'Year': week['Year'],
            'Week': week['Week'],
            'Month': month,
            'Season_Autumn': 1 if season_from_month(month) == 'Autumn' else 0,
            'Season_Spring': 1 if season_from_month(month) == 'Spring' else 0,
            'Season_Summer': 1 if season_from_month(month) == 'Summer' else 0,
            'Season_Winter': 1 if season_from_month(month) == 'Winter' else 0
        })

        predictions.append(week_prediction)

    return predictions
