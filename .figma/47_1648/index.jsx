import React from 'react';

import styles from './index.module.scss';

const Component = () => {
  return (
    <div className={styles.adminMengklikLogout}>
      <div className={styles.rectangle182}>
        <div className={styles.rectangle6}>
          <div className={styles.ionNotifcations}>
            <img src="../image/mkmr4xvg-xf94ju3.svg" className={styles.frame1} />
          </div>
          <p className={styles.admin}>Admin</p>
          <img src="../image/mkmr4xvg-k0x9k1l.svg" className={styles.group} />
        </div>
        <div className={styles.group11}>
          <div className={styles.rectangle16}>
            <div className={styles.autoWrapper}>
              <p className={styles.selamatDatangAdmin}>Selamat Datang, Admin.</p>
              <div className={styles.rectangle18}>
                <img
                  src="../image/mkmr4xvg-30rz597.svg"
                  className={styles.mdiBookOpenVariantOu}
                />
                <p className={styles.bukuSaku}>Buku Saku</p>
              </div>
              <div className={styles.rectangle36}>
                <p className={styles.penjelasanDetailMeng}>
                  Penjelasan Detail Mengenai Buku Saku
                </p>
              </div>
            </div>
            <div className={styles.rectangle39}>
              <p className={styles.listPengawasYangSeda}>
                List Pengawas yang sedang aktif
                <br />
              </p>
            </div>
            <div className={styles.autoWrapper2}>
              <div className={styles.rectangle41}>
                <p className={styles.riwayatTerbaru}>Riwayat Terbaru</p>
              </div>
              <div className={styles.rectangle40}>
                <p className={styles.listPengawasYangSeda}>
                  Riwayat Terbaru Mengenai Tambahan Model/Edit/Hapus Beserta
                  informasi waktu
                  <br />
                  <br />
                </p>
              </div>
            </div>
            <div className={styles.rectangle26}>
              <p className={styles.apakahAndaYakinIngin}>
                Apakah Anda Yakin Ingin Logout?
              </p>
              <div className={styles.autoWrapper3}>
                <div className={styles.rectangle43}>
                  <p className={styles.riwayatTerbaru}>Ya</p>
                </div>
                <div className={styles.rectangle432}>
                  <p className={styles.tidak}>&nbsp;Tidak</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className={styles.rectangle42}>
          <div className={styles.autoWrapper4}>
            <img
              src="../image/mkmr4xvg-dyb7yfx.svg"
              className={styles.mdiSettings}
            />
            <img src="../image/mkmr4xvg-lcppolv.svg" className={styles.mdiLogout} />
          </div>
          <div className={styles.autoWrapper5}>
            <p className={styles.profilAkun}>Profil Akun</p>
            <p className={styles.logout}>Logout</p>
          </div>
        </div>
      </div>
      <div className={styles.rectangle13}>
        <img
          src="../image/mkmr4xvq-72ngt0x.png"
          className={styles.logoPertaminaGasNega}
        />
        <div className={styles.rectangle162}>
          <div className={styles.lineMdHomeTwotone}>
            <img src="../image/mkmr4xvg-aqkf2om.svg" className={styles.vector} />
            <img src="../image/mkmr4xvg-wifth6m.svg" className={styles.group2} />
            <img
              src="../image/mkmr4xvg-qj6ikpr.svg"
              className={styles.materialSymbolsHomeR}
            />
          </div>
          <p className={styles.beranda}>Beranda</p>
        </div>
        <div className={styles.autoWrapper7}>
          <div className={styles.mdiAccountEyeOutline}>
            <img src="../image/mkmr4xvg-xto0yzz.svg" className={styles.vector2} />
          </div>
          <div className={styles.autoWrapper6}>
            <p className={styles.history}>History</p>
            <p className={styles.history2}>History</p>
          </div>
        </div>
        <div className={styles.autoWrapper8}>
          <img src="../image/mkmr4xvg-i6mvifi.svg" className={styles.group3} />
          <p className={styles.managamentUser}>Managament User</p>
        </div>
        <div className={styles.autoWrapper9}>
          <img src="../image/mkmr4xvg-1er810j.svg" className={styles.vector3} />
          <p className={styles.managamentUser}>Integrasi Sistem</p>
        </div>
      </div>
      <p className={styles.beranda2}>Beranda</p>
      <p className={styles.managamentUser2}>Managament User</p>
    </div>
  );
}

export default Component;
