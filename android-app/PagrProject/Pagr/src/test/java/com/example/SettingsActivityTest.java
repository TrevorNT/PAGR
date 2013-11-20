package com.example;

import android.content.Intent;
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import com.pivotallabs.injected.InjectedActivity;
import com.pivotallabs.tracker.RecentActivityActivity;
import org.junit.Before;
import org.junit.Test;
import org.junit.runner.RunWith;
import org.robolectric.Robolectric;
import org.robolectric.shadows.ShadowActivity;
import org.robolectric.shadows.ShadowIntent;

import edu.rpi.pagr.GuestNumbersActivity;
import edu.rpi.pagr.MakeReservationActivity;
import edu.rpi.pagr.SettingsActivity;

import static org.hamcrest.CoreMatchers.equalTo;
import static org.junit.Assert.assertNotNull;
import static org.junit.Assert.assertThat;
import static org.robolectric.Robolectric.clickOn;
import static org.robolectric.Robolectric.shadowOf;

import static org.fest.assertions.api.ANDROID.assertThat;

@RunWith(RobolectricGradleTestRunner.class)
public class SettingsActivityTest {
    private SettingsActivity activity;
    private android.content.SharedPreferences sharedPreferences;
    private android.content.SharedPreferences.Editor editor;
    public static final String SHARED_PREFERENCES = "edu.rpi.pagr.SHARED_PREFERENCES";
    public static final String KEY_SAVED_NAME = "edu.rpi.pagr.KEY_SAVED_NAME";

    @Before
    public void setUp() throws Exception {
        activity = Robolectric.buildActivity(SettingsActivity.class).create().visible().get();
        sharedPreferences = getSharedPreferences(SHARED_PREFERENCES, Context.MODE_PRIVATE);
    }


    @Test
    public void shouldExistsGuestNameDaniel() throws Exception {

        String customerName = mPrefs.getString(KEY_SAVED_NAME, null);
        assertThat(customerName, equalTo("Daniel"));
    }
}