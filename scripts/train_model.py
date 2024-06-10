from flask import Flask, request, jsonify
import os
import sys
import json
import pandas as pd
from sklearn.ensemble import GradientBoostingRegressor
from sklearn.model_selection import GridSearchCV
from joblib import dump
import script_date

app = Flask(__name__)

model_dir = '../storage/'

def train_model(data):
    try:
        df = pd.DataFrame(data)

        for col in df.columns:
            df[col] = df[col].apply(lambda x: int(float(x)) if pd.notnull(x) else 0)

        df['Month'] = df.apply(lambda row: script_date.month_from_week(row['Year'], row['Week']), axis=1)
        df['Season'] = df['Month'].apply(script_date.season_from_month)

        df = pd.get_dummies(df, columns=['Season'])

        X = df[['Year', 'Week', 'Month'] + [col for col in df.columns if 'Season_' in col]]
        y_columns = [
                'count_ct', 'count_mri', 'count_rg', 'count_flg', 'count_mmg', 'count_densitometer',
                'count_ct_with_ku_1_zone', 'count_mri_with_ku_1_zone', 'count_ct_with_ku_more_than_1_zone', 'count_mri_with_ku_more_than_1_zone'
        ]

        models = {}
        param_grid = {
                'n_estimators': [100, 200, 300],
                'learning_rate': [0.05, 0.1, 0.2],
                'max_depth': [3, 4, 5],
                'min_samples_split': [2, 5, 10],
                'min_samples_leaf': [1, 2, 4],
                'subsample': [0.8, 0.9, 1.0]
        }
        for column in y_columns:
            mask = df[column] != 0
            X_filtered = X[mask]
            y_filtered = df[column][mask]
            model = GradientBoostingRegressor()
            grid_search = GridSearchCV(estimator=model, param_grid=param_grid, cv=5, n_jobs=-1, scoring='neg_mean_squared_error')
            grid_search.fit(X_filtered, y_filtered)
            best_model = grid_search.best_estimator_

            models[column] = best_model

        for key, model in models.items():
            model_path = os.path.join(model_dir, f"{key}_model.pkl")
            dump(model, model_path)

        return "Model trained successfully"

    except Exception as e:
        return str(e)

@app.route('/python/train', methods=['POST'])
def run_script():
    data = request.json.get('data')
    result = train_model(data)
    if result == "Model trained successfully":
        return jsonify({'status': 'success', 'result': result}), 200
    else:
        return jsonify({'status': 'error', 'error': result}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=8000)
