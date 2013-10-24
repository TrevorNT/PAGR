package edu.rpi.pagr;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;

import org.apache.http.HttpResponse;
import org.apache.http.HttpStatus;
import org.apache.http.NameValuePair;
import org.apache.http.StatusLine;
import org.apache.http.client.HttpClient;
import org.apache.http.client.entity.UrlEncodedFormEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.message.BasicNameValuePair;
import org.apache.http.util.EntityUtils;

import java.util.ArrayList;

import edu.rpi.pagr.utils.GatewayConnectionUtils;

/**
 * Created by Daniel Zhao on 9/23/13.
 */
public class GuestNumbersActivity extends Activity {
    private Button button_ok;
    private TextView user_greeting_text;
    private EditText edit_number_of_guests;
    private AsyncTask<Void, Void, String> mSubmitGuestNumberTask;
    private String WAITING_TIME;

    // Handle to SharedPreferences for this app
    SharedPreferences mPrefs;

    // Handle to a SharedPreferences editor
    SharedPreferences.Editor mEditor;

    public static final String SHARED_PREFERENCES = "edu.rpi.pagr.SHARED_PREFERENCES";

    // Keys for storing the "latitude and longitude" flag in shared preferences
    public static final String KEY_SAVED_NAME = "edu.rpi.pagr.KEY_SAVED_NAME";

    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_guest_number);

        user_greeting_text = (TextView) findViewById(R.id.user_greeting);

        // Open Shared Preferences
        mPrefs = getSharedPreferences(SHARED_PREFERENCES, Context.MODE_PRIVATE);

        // Get an editor
        mEditor = mPrefs.edit();

        edit_number_of_guests = (EditText) findViewById(R.id.edit_guest_number);

        if ( mPrefs.contains(KEY_SAVED_NAME) ) {
            String guestName = mPrefs.getString(KEY_SAVED_NAME, null);
            user_greeting_text.setText(getString(R.string.user_greeting, guestName));
        } else {
            user_greeting_text.setText(R.string.user_greeting_guest);
        }

        button_ok = (Button) findViewById(R.id.button_ok);
        button_ok.setOnClickListener(new View.OnClickListener() {
            public void onClick(View v) {
//                Toast.makeText( getBaseContext(), "Button clicked", Toast.LENGTH_SHORT).show();
                // Perform action on click
                if ( edit_number_of_guests.getText().toString() != null) {
                    mSubmitGuestNumberTask = new SubmitGuestNumberTask().execute();
                    edit_number_of_guests.setText(null);
                }
            }
        });
    }

    private class SubmitGuestNumberTask extends AsyncTask<Void, Void, String> {

        @Override
        protected String doInBackground(Void... params) {
            try {
                if ( isCancelled() )
                    return null;

                // Send Values
                ArrayList<NameValuePair> nameValuePairs = new ArrayList<NameValuePair>();
                nameValuePairs.add(new BasicNameValuePair("c_guestnumber", edit_number_of_guests.getText().toString() ) );

                HttpClient httpclient = new DefaultHttpClient();
                HttpPost httppost = new HttpPost(GatewayConnectionUtils.getSubmitGuestNumbers());

                httppost.setEntity(new UrlEncodedFormEntity(nameValuePairs));

                HttpResponse response = httpclient.execute(httppost);

                StatusLine status = response.getStatusLine();

                if (status.getStatusCode() == HttpStatus.SC_OK) {
                    return new String(EntityUtils.toByteArray(response.getEntity()), "ISO-8859-1");
                }
            } catch (Exception ignored) {
            }

            return null;
        }

        @Override
        protected void onPostExecute(String result) {

            mSubmitGuestNumberTask = null;

            if (result != null) {
//                Toast.makeText(GuestNumbersActivity.this, result, Toast.LENGTH_SHORT).show();
                Intent intent = new Intent(getBaseContext(), MakeReservationActivity.class);
                intent.putExtra(WAITING_TIME, result);
                startActivity(intent);
                finish();
            }
        }
    }

    public boolean onOptionsItemSelected(MenuItem item) {

        switch (item.getItemId()) {
            case R.id.action_settings:
                Intent intent = new Intent(getBaseContext(), SettingsActivity.class);
                startActivity(intent);
                return true;
            default:
                return super.onOptionsItemSelected(item);
        }
    }


    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        // Inflate the menu; this adds items to the action bar if it is present.
        getMenuInflater().inflate(R.menu.settings, menu);
        return true;
    }
}