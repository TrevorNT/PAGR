package edu.rpi.pagr;

import android.os.Bundle;
import android.content.Intent;

import com.actionbarsherlock.app.SherlockActivity;

import edu.rpi.pagr.utils.AppStateUtils;

public class MainActivity extends SherlockActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if ( AppStateUtils.getInstance(this).isOnline(this) ) {
            Intent intent = new Intent(MainActivity.this, GuestNumbersActivity.class);
            startActivity(intent);
            finish();
        } else {
            setContentView(R.layout.activity_no_internet);
        }
    }
}