import React from 'react';

import styles from './index.module.scss';

const Component = () => {
  return (
    <div className={styles.userMengklikProfile}>
      <div className={styles.rectangle182}>
        <div className={styles.rectangle6}>
          <div className={styles.ionNotifcations}>
            <img src="../image/mkmaknw1-dipp0g6.svg" className={styles.frame1} />
          </div>
          <p className={styles.user}>User</p>
          <img src="../image/mkmaknw1-6157kal.svg" className={styles.group} />
        </div>
        <div className={styles.group11}>
          <div className={styles.rectangle16}>
            <p className={styles.selamatDatangUser}>Selamat Datang, User.</p>
            <div className={styles.autoWrapper}>
              <div className={styles.rectangle18}>
                <img
                  src="../image/mkmaknw1-piondgm.svg"
                  className={styles.mdiBookOpenVariantOu}
                />
                <p className={styles.bukuSaku}>Buku Saku</p>
              </div>
              <div className={styles.rectangle41}>
                <p className={styles.riwayatTerbaru}>Riwayat Terbaru</p>
              </div>
            </div>
            <div className={styles.autoWrapper2}>
              <div className={styles.rectangle36}>
                <p className={styles.penjelasanDetailMeng}>
                  Penjelasan Detail Mengenai Buku Saku
                </p>
              </div>
              <div className={styles.rectangle40}>
                <p className={styles.riwayatTerbaruMengen}>
                  Riwayat Terbaru Mengenai Tambahan Model/Edit/Hapus Beserta
                  informasi waktu
                  <br />
                  <br />
                </p>
              </div>
            </div>
          </div>
        </div>
        <div className={styles.rectangle42}>
          <div className={styles.autoWrapper3}>
            <img
              src="../image/mkmaknw2-ho4ixwc.svg"
              className={styles.mdiSettings}
            />
            <img src="../image/mkmaknw2-ss8iowj.svg" className={styles.mdiLogout} />
          </div>
          <div className={styles.autoWrapper4}>
            <p className={styles.profilAkun}>Profil Akun</p>
            <p className={styles.logout}>Logout</p>
          </div>
        </div>
      </div>
      <div className={styles.rectangle13}>
        <img
          src="../image/mkmaknw7-ats2f2j.png"
          className={styles.logoPertaminaGasNega}
        />
        <div className={styles.rectangle162}>
          <div className={styles.lineMdHomeTwotone}>
            <img src="../image/mkmaknw1-yr0rga1.svg" className={styles.vector} />
            <img src="../image/mkmaknw1-icvqqs0.svg" className={styles.group2} />
            <img
              src="../image/mkmaknw1-i7j1mfe.svg"
              className={styles.materialSymbolsHomeR}
            />
          </div>
          <p className={styles.beranda}>Beranda</p>
        </div>
        <div className={styles.autoWrapper6}>
          <div className={styles.mdiAccountEyeOutline}>
            <img src="../image/mkmaknw1-cr7jptb.svg" className={styles.vector2} />
          </div>
          <div className={styles.autoWrapper5}>
            <p className={styles.history}>History</p>
            <p className={styles.history2}>History</p>
          </div>
        </div>
      </div>
      <p className={styles.beranda2}>Beranda</p>
    </div>
  );
}

export default Component;
