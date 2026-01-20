import React from 'react';

import styles from './index.module.scss';

const Component = () => {
  return (
    <div className={styles.integrasiSystemDataK}>
      <div className={styles.rectangle18}>
        <div className={styles.rectangle13}>
          <img
            src="../image/mkmako3u-gt3in4e.png"
            className={styles.logoPertaminaGasNega}
          />
          <div className={styles.autoWrapper}>
            <div className={styles.lineMdHomeTwotone}>
              <img src="../image/mkmako3t-vam6nn2.svg" className={styles.vector} />
              <img src="../image/mkmako3t-yimws88.svg" className={styles.group} />
              <img
                src="../image/mkmako3t-0g4ibdr.svg"
                className={styles.materialSymbolsHomeR}
              />
            </div>
            <p className={styles.beranda}>Beranda</p>
          </div>
          <div className={styles.autoWrapper2}>
            <img
              src="../image/mkmako3t-3vx4aab.svg"
              className={styles.materialSymbolsHisto}
            />
            <p className={styles.history}>History</p>
          </div>
          <div className={styles.rectangle16}>
            <p className={styles.tambahModulSystem}>Tambah Modul / System</p>
            <div className={styles.rectangle17}>
              <img
                src="../image/mkmako3t-t9jbqls.svg"
                className={styles.materialSymbolsHisto}
              />
              <p className={styles.integrasiSistem}>Integrasi Sistem</p>
            </div>
          </div>
        </div>
        <div className={styles.autoWrapper5}>
          <div className={styles.rectangle6}>
            <p className={styles.dashboardIntegrasiSi}>
              Dashboard → Integrasi Sistem
            </p>
            <img src="../image/mkmako3t-63wmqf1.svg" className={styles.vector2} />
            <div className={styles.autoWrapper3}>
              <img src="../image/mkmako3t-yk33m7l.svg" className={styles.group2} />
              <p className={styles.supervisor}>Supervisor</p>
            </div>
          </div>
          <div className={styles.rectangle42}>
            <p className={styles.manajemenModulAplika}>Manajemen Modul Aplikasi</p>
            <div className={styles.autoWrapper4}>
              <div className={styles.rectangle46}>
                <p className={styles.hapusModul}>Hapus Modul</p>
              </div>
              <div className={styles.rectangle45}>
                <p className={styles.hapusModul}>Tambah Modul</p>
              </div>
              <div className={styles.rectangle44}>
                <p className={styles.cariModul}>Cari Modul...</p>
                <img
                  src="../image/mkmako3t-i9bzvdw.svg"
                  className={styles.icSharpSearch}
                />
              </div>
            </div>
            <div className={styles.rectangle47}>
              <img
                src="../image/mkmako3t-msgfemx.svg"
                className={styles.iconoirDbError}
              />
              <p className={styles.daftarAplikasiPgnOne}>
                Daftar aplikasi PGN One Portal masih kosong. Yuk, tambahkan modul
                atau sistem pertama Anda agar bisa diakses oleh pengguna.
              </p>
            </div>
          </div>
        </div>
      </div>
      <div className={styles.autoWrapper6}>
        <p className={styles.beranda}>Beranda</p>
        <p className={styles.history2}>History</p>
      </div>
    </div>
  );
}

export default Component;
