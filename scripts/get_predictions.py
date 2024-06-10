import os
import sys
import json
import pandas as pd
import numpy as np
from joblib import load
import datetime

model_dir = '../storage/'

# Определение количества дней в неделе
def days_in_week(year, month, week):
    _, days_in_month = calendar.monthrange(year, month)
    first_weekday = calendar.weekday(year, month, 1)
    weeks_in_month = (days_in_month + first_weekday) // 7 + 1 if (days_in_month + first_weekday) % 7 != 0 else (days_in_month + first_weekday) // 7

    if week > weeks_in_month:
        return 0

    first_day_of_week = 1 + (week - 1) * 7 - first_weekday
    last_day_of_week = min(first_day_of_week + 6, days_in_month)

    days_in_target_week = last_day_of_week - first_day_of_week + 1

    if (first_day_of_week >= 1 and first_day_of_week <= days_in_month) and (last_day_of_week >= 1 and last_day_of_week <= days_in_month):
        return days_in_target_week
    elif first_day_of_week < 1:
        days_in_target_week -= abs(first_day_of_week)
    elif last_day_of_week > days_in_month:
        days_in_target_week -= last_day_of_week - days_in_month

    return days_in_target_week - 1

# Определения недель в месяце
def get_weeks_in_month(year, month):
    start_date = datetime.date(year, month, 1)
    if month == 12:
        end_date = datetime.date(year + 1, 1, 1) - datetime.timedelta(days=1)
    else:
        end_date = datetime.date(year, month + 1, 1) - datetime.timedelta(days=1)

    weeks = []
    current_date = start_date
    next_week_num = None
    while current_date <= end_date:
        week_num = current_date.isocalendar()[1]

        if current_date.month == 1 and week_num == 52:
            current_year = year - 1
        else:
            current_year = year

        first_day_of_week = current_date - datetime.timedelta(days=current_date.weekday())
        last_day_of_week = first_day_of_week + datetime.timedelta(days=6)

        if first_day_of_week < start_date:
            first_day_of_week = start_date
        if last_day_of_week > end_date:
            last_day_of_week = end_date

        weeks.append({
            'Year': current_year,
            'Week': week_num
        })

        current_date = last_day_of_week + datetime.timedelta(days=1)

    return weeks

model_paths = [
    'count_ct_model.pkl', 'count_mri_model.pkl', 'count_rg_model.pkl', 'count_flg_model.pkl', 'count_mmg_model.pkl', 'count_densitometer_model.pkl',
    'count_ct_with_ku_1_zone_model.pkl', 'count_mri_with_ku_1_zone_model.pkl', 'count_ct_with_ku_more_than_1_zone_model.pkl', 'count_mri_with_ku_more_than_1_zone_model.pkl'
]

models = [load(os.path.join(model_dir, model_path)) for model_path in model_paths]

def main(year, month):
    weeks = get_weeks_in_month(year, month)
    predictions = []

    for week in weeks:
        days_in_current_week = days_in_week(week['Year'], week['Month'], week['Week'])
        month = month_from_week(week['Year'], week['Week'])
        week_features = pd.DataFrame([{
            'Year': week['Year'],
            'Week': week['Week'],
            'Days_in_Week': days_in_current_week,
            'Month': month
        }])

        week_prediction = {model_name: int(model.predict(week_features)[0] * days_in_current_week / 7)
                           for model, model_name in zip(models, ['ct', 'mri', 'rg', 'flg', 'mmg', 'densitometer', 'ct_with_ku_1_zone', 'mri_with_ku_1_zone', 'ct_with_ku_more_than_1_zone', 'mri_with_ku_more_than_1_zone'])}

        week_prediction.update({'Week': week['Week'], 'Year': week['Year']})
        predictions.append(week_prediction)

    print(json.dumps(predictions, ensure_ascii=False))

if __name__ == "__main__":
    year = int(sys.argv[1])
    month = int(sys.argv[2])
    main(year, month)
