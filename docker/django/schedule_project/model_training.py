import asyncio
from concurrent.futures import ThreadPoolExecutor
import pandas as pd
from sklearn.ensemble import GradientBoostingRegressor
from sklearn.model_selection import RandomizedSearchCV
from sklearn.metrics import make_scorer, mean_squared_error
from joblib import dump
import numpy as np
import datetime
import logging
import random

logging.basicConfig(filename='./model_training.log', level=logging.INFO,
                    format='%(asctime)s %(message)s')

def month_from_week(year, week):
    first_day_of_week = datetime.datetime.strptime(f'{year}-W{week}-1', "%Y-W%U-%w")
    return first_day_of_week.month

def season_from_month(month):
    season_mapping = {
        12: 'Winter', 1: 'Winter', 2: 'Winter',
        3: 'Spring', 4: 'Spring', 5: 'Spring',
        6: 'Summer', 7: 'Summer', 8: 'Summer',
        9: 'Autumn', 10: 'Autumn', 11: 'Autumn'
    }
    return season_mapping[month]

def generate_param_dist():
    param_dist = {
        'n_estimators': np.random.choice([500, 1000, 1500, 2000], size=2),
        'learning_rate': np.random.choice([0.01, 0.05, 0.1, 0.2, 0.001], size=2),
        'max_depth': np.random.choice([6, 8, 10, 12], size=2),
        'min_samples_split': np.random.choice([2, 5, 10, 15], size=2),
        'min_samples_leaf': np.random.choice([1, 2, 4, 8], size=2),
        'subsample': np.random.choice([0.6, 0.7, 0.8, 0.9], size=2),
        'max_features': np.random.choice(['sqrt', 'log2', None], size=2),
        'warm_start': [True, False]
    }
    return param_dist

def non_negative_error(y_true, y_pred):
    return mean_squared_error(y_true, y_pred)

def train_single_model(column, df, X, param_dist):
    mask = df[column] != 0
    X_filtered = X[mask]
    y_filtered = df[column][mask]
    y_filtered = np.maximum(y_filtered, 1e-9)

    while True:
        try:
            model = GradientBoostingRegressor(random_state=42)
            random_search = RandomizedSearchCV(estimator=model, param_distributions=param_dist, n_iter=50, cv=5, n_jobs=-1, scoring=make_scorer(non_negative_error), random_state=42)
            random_search.fit(X_filtered, y_filtered)
            best_model = random_search.best_estimator_

            predictions = best_model.predict(X_filtered)
            predictions = np.maximum(predictions, 1e-9)
            actual_values = y_filtered.values

            # Проверка условия на подвыборке
            sample_size = min(200, len(predictions))
            indices = np.random.choice(len(predictions), sample_size, replace=False)
            predictions_sample = predictions[indices]
            actual_values_sample = actual_values[indices]

            random_state = np.random.RandomState(seed=42)
            min_thresholds = actual_values_sample * 0.9
            random_offsets = random_state.uniform(low=0, high=1, size=len(actual_values_sample))
            predictions_limited = np.maximum(min_thresholds + random_offsets, actual_values_sample)

            condition_met = np.all((1 - predictions_sample / actual_values_sample) < 0.1) and np.all(predictions_sample > 0)

            if condition_met:
                model_path = f"./storage/{column}_model.pkl"
                dump(best_model, model_path)
                logging.info(f'Model for {column} trained and saved')
                return column, best_model
            else:
                logging.info(f'Model for {column} did not meet the conditions. Let\'s try again')

        except Exception as ex:
            logging.error(f"Error in training for {column}: {ex}")

        param_dist = generate_param_dist()

async def train_models_async(data):
    try:
        df = pd.DataFrame(data)

        for col in df.columns:
            df[col] = df[col].apply(lambda x: int(float(x)) if pd.notnull(x) else 0)

        df['Month'] = df.apply(lambda row: month_from_week(row['Year'], row['Week']), axis=1)
        df['Season'] = df['Month'].apply(season_from_month)

        df = pd.get_dummies(df, columns=['Season'])

        X = df[['Year', 'Week', 'Month'] + [col for col in df.columns if 'Season_' in col]]
        y_columns = [
            'count_ct', 'count_mri', 'count_rg', 'count_flg', 'count_mmg', 'count_densitometer',
            'count_ct_with_ku_1_zone', 'count_mri_with_ku_1_zone', 'count_ct_with_ku_more_than_1_zone', 'count_mri_with_ku_more_than_1_zone'
        ]

        models = {}

        param_dist = generate_param_dist()

        loop = asyncio.get_event_loop()
        executor = ThreadPoolExecutor(max_workers=5)

        async def async_train_single_model(column):
            logging.info(f'Starting training for: {column}')
            result = await loop.run_in_executor(executor, train_single_model, column, df, X, param_dist)
            if result is None:
                logging.info(f'Finished training for: {column} with NONE result')
            else:
                column, best_model = result
                models[column] = best_model
                logging.info(f'Successfully finished training for: {column}')
            return result

        tasks = [async_train_single_model(column) for column in y_columns]
        logging.info('Tasks created, gathering results...')
        results = await asyncio.gather(*tasks)

        for column, best_model in results:
            if best_model is not None:
                models[column] = best_model

        logging.info("Prediction model training script completed successfully")
        return "success"

    except Exception as e:
        logging.error(f"An error occurred: {e}")
        return str(e)
