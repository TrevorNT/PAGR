package edu.rpi.pagr;

import android.os.Bundle;
import android.app.Activity;
import android.view.Menu;
import android.content.Intent;

import com.actionbarsherlock.app.SherlockActivity;
import com.actionbarsherlock.view.MenuInflater;

import edu.rpi.pagr.utils.AppState;

public class MainActivity extends SherlockActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if ( AppState.getInstance(this).isOnline(this) ) {
            Intent intent = new Intent(MainActivity.this, GuestNumbersActivity.class);
            startActivity(intent);
            finish();
        } else {
            setContentView(R.layout.activity_no_internet);
//            finish();
        }
    }


//    @Override
//    public boolean onCreateOptionsMenu(Menu menu) {
//        // Inflate the menu; this adds items to the action bar if it is present.
//        MenuInflater inflater = getSupportMenuInflater();
//        getSupportMenuInflater().inflate(R.menu.settings, menu);
//        return super.onCreateOptionsMenu(menu);
//    }

}