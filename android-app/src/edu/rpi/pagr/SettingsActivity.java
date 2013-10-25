package edu.rpi.pagr;

import android.content.Context;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.app.Activity;
import android.support.v4.app.NavUtils;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;

public class SettingsActivity extends Activity {

    private EditText nameText;
    private Button button_save;

    // Handle to SharedPreferences for this app
    SharedPreferences mPrefs;

    // Handle to a SharedPreferences editor
    SharedPreferences.Editor mEditor;

    public static final String SHARED_PREFERENCES = "edu.rpi.pagr.SHARED_PREFERENCES";

    // Keys for storing the "latitude and longitude" flag in shared preferences
    public static final String KEY_SAVED_NAME = "edu.rpi.pagr.KEY_SAVED_NAME";

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);

        getActionBar().setDisplayHomeAsUpEnabled(true);

        nameText = (EditText) findViewById(R.id.editName);
        button_save = (Button) findViewById(R.id.button_save);

        // Open Shared Preferences
        mPrefs = getSharedPreferences(SHARED_PREFERENCES, Context.MODE_PRIVATE);

        // Get an editor
        mEditor = mPrefs.edit();

        if (mPrefs.contains(KEY_SAVED_NAME)) {
            nameText.setText( mPrefs.getString(KEY_SAVED_NAME, null));
        }

        button_save.setOnClickListener( new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (nameText != null) {
                    mEditor.putString(KEY_SAVED_NAME, nameText.getText().toString());
                    mEditor.commit();
                }
            }
        });
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.settings, menu);
        return true;
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        switch (item.getItemId()) {
            // Respond to the action bar's Up/Home button
            case android.R.id.home:
                NavUtils.navigateUpFromSameTask(this);
                return true;
        }
        return super.onOptionsItemSelected(item);
    }
    
}
